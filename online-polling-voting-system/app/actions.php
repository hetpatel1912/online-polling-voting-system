<?php
declare(strict_types=1);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
$action=post('action');
$back=in_array($action,['login','register'],true)?'index.php?page='.$action:'index.php';
try {
    if (!hash_equals(csrf(),post('csrf'))) throw new RuntimeException('Your session expired. Refresh the page and try again.');
    if ($action==='register' || $action==='login') {
        $email=strtolower(post('email')); $password=is_string($_POST['password']??null)?$_POST['password']:'';
        if (!filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($email)>190) throw new RuntimeException('Enter a valid email address.');
        // Both an IP and an account bucket limit repeated authentication attempts.
        $buckets=[hash('sha256','ip:'.($_SERVER['REMOTE_ADDR']??'local')),hash('sha256','email:'.$email)];
        query('DELETE FROM login_limits WHERE first_attempt < NOW() - INTERVAL 15 MINUTE');
        foreach ($buckets as $bucket) {
            query('INSERT INTO login_limits (bucket) VALUES (?) ON DUPLICATE KEY UPDATE attempts=attempts+1',[$bucket]);
            if ((int)query('SELECT attempts FROM login_limits WHERE bucket=?',[$bucket])->fetchColumn()>20) throw new RuntimeException('Too many attempts. Try again in 15 minutes.');
        }
        if ($action==='register') {
            $name=post('name');
            if (strlen($name)<2 || strlen($name)>100 || strlen($password)<8 || strlen($password)>72) throw new RuntimeException('Use a name of 2–100 characters and a password of 8–72 characters.');
            try { query('INSERT INTO users (name,email,password_hash) VALUES (?,?,?)',[$name,$email,password_hash($password,PASSWORD_DEFAULT)]); }
            catch (PDOException $ex) { if (($ex->errorInfo[1]??0)===1062) throw new RuntimeException('That email is already registered. Please sign in.'); throw $ex; }
            $uid=(int)db()->lastInsertId();
        } else {
            $u=query('SELECT * FROM users WHERE email=?',[$email])->fetch();
            if (!$u || !password_verify($password,$u['password_hash']) || !$u['active']) throw new RuntimeException('Incorrect email or password, or account disabled.');
            $uid=(int)$u['id'];
        }
        session_regenerate_id(true); $_SESSION['uid']=$uid; $_SESSION['csrf']=bin2hex(random_bytes(32));
        flash($action==='register'?'Your account is ready. Make your voice heard.':'Welcome back.'); go();
    }
    if ($action==='logout') { $_SESSION=[]; session_regenerate_id(true); go(); }
    if ($action==='vote') {
        $u=require_user(); $id=(int)post('poll_id'); $back='index.php?page=poll&id='.$id;
        db()->beginTransaction(); $p=read_poll($id,true);
        if (status($p)!=='Active') throw new RuntimeException('This poll is not accepting votes.');
        if (!query('SELECT id FROM poll_options WHERE id=? AND poll_id=?',[(int)post('option_id'),$id])->fetch()) throw new RuntimeException('Choose one of the available options.');
        if (query('SELECT id FROM votes WHERE user_id=? AND poll_id=?',[$u['id'],$id])->fetch()) throw new RuntimeException('You have already voted in this poll.');
        query('INSERT INTO votes (user_id,poll_id,option_id) VALUES (?,?,?)',[$u['id'],$id,(int)post('option_id')]);
        db()->commit(); flash('Vote recorded successfully. Results will be published when the poll closes.'); go($back);
    }
    $admin=require_admin(); $back='index.php?page=admin';
    if ($action==='save_poll') {
        [$title,$description,$category,$start,$end,$options]=poll_input(); $id=(int)post('poll_id');
        db()->beginTransaction();
        if ($id) {
            $p=read_poll($id,true);
            if (status($p)==='Closed' || (int)query('SELECT COUNT(*) FROM votes WHERE poll_id=?',[$id])->fetchColumn()>0) throw new RuntimeException('Only open or upcoming polls with no votes can be edited.');
            query('UPDATE polls SET title=?,description=?,category=?,starts_at=?,ends_at=? WHERE id=?',[$title,$description,$category,$start,$end,$id]);
            query('DELETE FROM poll_options WHERE poll_id=?',[$id]);
        } else {
            query('INSERT INTO polls (title,description,category,starts_at,ends_at,created_by) VALUES (?,?,?,?,?,?)',[$title,$description,$category,$start,$end,$admin['id']]);
            $id=(int)db()->lastInsertId();
        }
        foreach ($options as $option) query('INSERT INTO poll_options (poll_id,label) VALUES (?,?)',[$id,$option]);
        db()->commit(); flash('Poll saved successfully.'); go($back);
    }
    if ($action==='close_poll') {
        db()->beginTransaction(); read_poll((int)post('poll_id'),true);
        query('UPDATE polls SET closed=1 WHERE id=?',[(int)post('poll_id')]); db()->commit();
        flash('Poll closed. Results are now available.'); go($back);
    }
    if ($action==='toggle_user') {
        // Administrators cannot disable themselves or any other administrator.
        query("UPDATE users SET active=NOT active WHERE id=? AND role='voter'",[(int)post('user_id')]);
        flash('Account status updated.'); go('index.php?page=users');
    }
    throw new RuntimeException('Unknown action.');
} catch (Throwable $ex) {
    if (db()->inTransaction()) db()->rollBack();
    if ($ex instanceof PDOException) { error_log($ex->getMessage()); flash('Unable to save this request. Please try again.','error'); }
    else flash($ex->getMessage(),'error');
    // Preserve poll fields on a validation failure, but never passwords.
    if ($action==='save_poll') { $_SESSION['draft']=array_intersect_key($_POST,array_flip(['title','description','category','starts_at','ends_at','options','poll_id'])); $back='index.php?page=edit'.((int)post('poll_id')?'&id='.(int)post('poll_id'):''); }
    go($back);
}
