<?php 
use ItForFree\SimpleMVC\Config;

$Url = Config::getObject('core.router.class');
$User = Config::getObject('core.user.class');
?>

<?php include('includes/admin-users-nav.php'); ?>


<h2><?= $editAdminusersTitle ?>
    <span>
        <?= $User->returnIfAllowed("admin/adminusers/delete", 
            "<a href=" . $Url::link("admin/adminusers/delete&id=" . $_GET['id']) 
            . ">[Удалить]</a>");?>
    </span>
</h2>
<?php if ($viewAdminusers->lastEdited || $viewAdminusers->timestamp): ?>
    <p>
        <strong>Дата создания:</strong> 
        <?= $viewAdminusers->timestamp ? date('d.m.Y H:i', strtotime($viewAdminusers->timestamp)) : 'Не указано' ?>
        
        <?php if ($viewAdminusers->lastEdited && $viewAdminusers->lastEdited != $viewAdminusers->timestamp): ?>
            <br>
            <strong>Последнее редактирование:</strong> 
            <?= date('d.m.Y H:i', strtotime($viewAdminusers->lastEdited)) ?>
        <?php endif; ?>
    </p>
<?php endif; ?>
<form id="editUser" method="post" action="<?= $Url::link("admin/adminusers/edit&id=" . $_GET['id'])?>">
    <h5>Введите имя пользователя</h5>
    <input type="text" name="login" placeholder="логин пользователя" value="<?= $viewAdminusers->login ?>"><br>
    <h5>Введите пароль</h5>
    <input type="text" name="pass" placeholder="новый пароль" value=""><br>
    <h5>Введите e-mail</h5>
    <input type="text" name="email"  placeholder="email" value="<?= $viewAdminusers->email ?>"><br>
    
     <h5>Выберите роль</h5>
    <select name="role" required>
        <option value="admin" <?= $viewAdminusers->role == 'admin' ? 'selected' : '' ?>>Администратор</option>
        <option value="auth_user" <?= $viewAdminusers->role == 'auth_user' ? 'selected' : '' ?>>Зарегистрированный пользователь</option>
    </select><br>

    <input type="hidden" name="id" value="<?= $_GET['id']; ?>">
    <input type="submit" name="saveChanges" value="Сохранить">
    <input type="submit" name="cancel" value="Назад">
</form>

