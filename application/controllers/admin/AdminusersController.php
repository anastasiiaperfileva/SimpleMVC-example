<?php
namespace application\controllers\admin;
use ItForFree\SimpleMVC\Config;
use \application\models\UserModel;

/**
 * Администрирование пользователей
 */
class AdminusersController extends \ItForFree\SimpleMVC\MVC\Controller
{
    
    public string $layoutPath = 'admin-main.php';
    
    protected array $rules = [ //вариант 2:  здесь всё гибче, проще развивать в дальнешем
         ['allow' => true, 'roles' => ['admin']],
         ['allow' => false, 'roles' => ['?', '@']],
    ];
    
    /**
     * Основное действие контроллера
     */
    public function indexAction()
    {
        $Adminusers = new UserModel();
        $userId = $_GET['id'] ?? null;
        
        if ($userId) { // если указан конктреный пользователь
            $viewAdminusers = $Adminusers->getById($_GET['id']);
            $this->view->addVar('viewAdminusers', $viewAdminusers);
            $this->view->render('user/view-item.php');
        } else { // выводим полный список
            
           $users = $Adminusers->getList()['results'];
            $this->view->addVar('users', $users);
            $this->view->render('user/index.php');
        }
    }

    /**
     * Создание нового пользователя
     */
    public function addAction()
    {
        $Url = Config::get('core.router.class');
        if (!empty($_POST)) {
            if (!empty($_POST['saveNewUser'])) {
                $Adminusers = new UserModel();
                $newAdminusers = $Adminusers->loadFromArray($_POST);
                $newAdminusers->insert(); 
                $this->redirect($Url::link("admin/adminusers/index"));
            } 
            elseif (!empty($_POST['cancel'])) {
                $this->redirect($Url::link("admin/adminusers/index"));
            }
        } else {
            $addAdminusersTitle = "Регистрация пользователя";
            $this->view->addVar('addAdminusersTitle', $addAdminusersTitle);
            
            $this->view->render('user/add.php');
        }
    }
    
    /**
     * Редактирование пользователя
     */
    public function editAction()
    {
        $id = $_GET['id'];
        $Url = Config::get('core.router.class');
         $Adminusers = new UserModel(); 
        
        if (!empty($_POST)) { // это выполняется нормально.
            
            if (!empty($_POST['saveChanges'] )) {
                $currentUser = $Adminusers->getById($id);
                $newAdminusers = $Adminusers->loadFromArray($_POST);
                 $newAdminusers->id = $id;
                 

                 if (isset($_POST['role'])) {
                    $newAdminusers->role = $_POST['role'];
                }

                $dataChanged = false;
                if ($currentUser->login != $_POST['login'] ||
                    $currentUser->email != $_POST['email'] ||
                    $currentUser->role != $_POST['role'] ||
                    !empty($_POST['pass'])) {
                    $dataChanged = true;
                }


                if (empty($_POST['pass'])){
                    $currentUser = $Adminusers->getById($id);
                    $newAdminusers->pass = $currentUser->pass;
                    $newAdminusers->salt = $currentUser->salt;

                }
                else{
                    $dataChanged = true;
                    $newAdminusers->salt = rand(0, 1000000);
                    $saltedPassword = $_POST['pass'] . $newAdminusers->salt;
                    $newAdminusers->pass = password_hash($saltedPassword, PASSWORD_BCRYPT);
                }
                $newAdminusers->update();
                $this->redirect($Url::link("admin/adminusers/index&id=$id"));

            } 
            elseif (!empty($_POST['cancel'])) {
                $Adminusers->updateLastEdited($id);
                $this->redirect($Url::link("admin/adminusers/index&id=$id"));
            }
        } else {
            $Adminusers->updateLastEdited($id);

            $Adminusers = new UserModel();
            $viewAdminusers = $Adminusers->getById($id);
            
            $editAdminusersTitle = "Редактирование данных пользователя";
            
            $this->view->addVar('viewAdminusers', $viewAdminusers);
            $this->view->addVar('editAdminusersTitle', $editAdminusersTitle);
            
            $this->view->render('user/edit.php');   
        }
        
    }
    
    /**
     * Удаление пользователя
     */
    public function deleteAction()
    {
        $id = $_GET['id'];
        $Url = Config::get('core.router.class');
        
        if (!empty($_POST)) {
            if (!empty($_POST['deleteUser'])) {
                $Adminusers = new UserModel();
                $newAdminusers = $Adminusers->loadFromArray($_POST);
                $newAdminusers->delete();
                
                $this->redirect($Url::link("admin/adminusers/index"));
              
            }
            elseif (!empty($_POST['cancel'])) {
                $this->redirect($Url::link("admin/adminusers/edit&id=$id"));
            }
        } else {
            
            $Adminusers = new UserModel();
            $deletedAdminusers = $Adminusers->getById($id);
            $deleteAdminusersTitle = "Удаление статьи";
            
            $this->view->addVar('deleteAdminusersTitle', $deleteAdminusersTitle);
            $this->view->addVar('deletedAdminusers', $deletedAdminusers);
            
            $this->view->render('user/delete.php');
        }
    }
}
