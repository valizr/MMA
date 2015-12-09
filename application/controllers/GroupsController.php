<?php

class GroupsController extends Zend_Controller_Action {

    public function init() {
        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $this->view->message = $this->_flashMessenger->getMessages();
        require_once APPLICATION_PUBLIC_PATH . '/library/tsThumb/ThumbLib.inc.php';
    }

    public function indexAction() {
        $auth = Zend_Auth::getInstance();
        $authAccount = $auth->getStorage()->read();

        $params = array();
        $conditions = array();

        if ($this->getRequest()->getParam('nameSearch')) {
            $params['nameSearch'] = $this->getRequest()->getParam('nameSearch');
        }
        //BEGIN:SELECT GROUPS

        $conditions['pagination'] = true;

        $groups = new Default_Model_Groups();
        $select = $groups->getMapper()->getDbTable()->select()
            ->from(array('g' => 'groups'), array('g.id', 'g.name', 'g.created', 'g.deleted'))
            ->joinLeft(array('pg' => 'product_groups'), 'g.id = pg.idGroup', array('productsNr' => 'COUNT(pg.id)'))
            ->where('NOT g.deleted');
        if (!empty($params['nameSearch'])) {
            $select->where('name LIKE ?', '%' . $params['nameSearch'] . '%');
        }
        $select->group('g.id');
        $select->setIntegrityCheck(false);
        $select->order(array('name ASC'));
        //END:SELECT GROUPS

        $form = new Default_Form_Groups();
        $form->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/groups/add-group.phtml'))));
        $this->view->form = $form;
        $this->view->color = "#fff";
        //$this->view->search=$params;
        if ($this->getRequest()->getParam('idGroup')) {
            $id = $this->getRequest()->getParam('idGroup');
            $model = new Default_Model_Groups();
            if ($model->find($id)) {
                $form = new Default_Form_Groups();

                $form->edit($model);
                $form->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/groups/edit-group.phtml'))));
                $this->view->form = $form;
                $this->view->color = $model->getColor();
                $this->view->idGroup = $model->getId();
                if ($this->getRequest()->isPost()) {
                    if ($this->getRequest()->getPost('submit')) {
                        if ($form->isValid($this->getRequest()->getPost())) {
                            $model->setOptions($form->getValues());
                            if ($groupId = $model->save()) {
                                //mesaj de succes
                                $this->_flashMessenger->addMessage("<div class='success  canhide'><p>Group was modified successfully<a href='javascript:;'></a></p></div>");
                            } else {
                                $this->_flashMessenger->addMessage("<div class='failure canhide'><p>Group was not modified<a href='javascript:;'></a></p></div>");
                            }
                            $this->_redirect(WEBROOT . 'groups');
                        }
                    }
                }
            }
        } elseif ($this->getRequest()->isPost()) {// && $this->getRequest()->getParam('action') == 'add'
            if ($form->isValid($this->getRequest()->getPost())) {
                $post = $this->getRequest()->getPost();
                $model = new Default_Model_Groups();
                $model->setOptions($form->getValues());

                if ($groupId = $model->save()) {
                    //mesaj de succes
                    $this->_flashMessenger->addMessage("<div class='success  canhide'><p>Group was added successfully<a href='javascript:;'></a><p></div>");
                } else {
                    //mesaj de eroare
                    $this->_flashMessenger->addMessage("<div class='failure canhide'><p>Group was not added<a href='javascript:;'></a><p></div>");
                }
                //redirect
                $this->_redirect(WEBROOT . 'groups');
            }
        }
        $formsearch = new Default_Form_GroupSearch();
        $formsearch->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/groups/group-search.phtml'))));
        $this->view->formsearch = $formsearch;
        $this->view->search = $params;

        // pagination
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
        $paginator->setItemCountPerPage(20);
        $paginator->setCurrentPageNumber($this->_getParam('page'));
        $paginator->setPageRange(5);
        Zend_Paginator::setDefaultScrollingStyle('Sliding');
        Zend_View_Helper_PaginationControl::setDefaultViewPartial(array('_pagination.phtml', array()));

        $this->view->result = $paginator;
        $this->view->itemCountPerPage = $paginator->getItemCountPerPage();
        $this->view->totalItemCount = $paginator->getTotalItemCount();
    }

    public function detailsAction() {
        $userId = null;
        $auth = Zend_Auth::getInstance();
        $authAccount = $auth->getStorage()->read();
        if (null != $authAccount) {
            if (null != $authAccount->getId()) {
                $this->view->userlogat = $authAccount;
            }
        }
        $id = (int) $this->getRequest()->getParam('id');
        if ($id) {
            // BEGIN: Find model
            $model = new Default_Model_Groups();
            if ($model->find($id)) {
                $this->view->result = $model;
            }
            $select = $model->getMapper()->getDbTable()->select()
                ->where('NOT deleted')
                ->order(array('created DESC'));
            $result = $model->fetchAll($select);
            // END: Find model
        }
    }

    public function editAction() {
        $auth = Zend_Auth::getInstance();
        $authAccount = $auth->getStorage()->read();
        if (null != $authAccount) {
            if (null != $authAccount->getId()) {
                $user = new Default_Model_Users();
                $user->find($authAccount->getId());
            }
        }

        $id = $this->getRequest()->getParam('id');
        /* 	$hasAccess = Needs_Roles::hasAccess(Zend_Registry::get('user')->getIdRole(),'adaugare_group');
          if(!$hasAccess)
          {
          $this->_redirect(WEBROOT.'groups');
          } */

        $model = new Default_Model_Groups();
        if ($model->find($id)) {
            $form = new Default_Form_Groups();

            $form->edit($model);
            $form->setDecorators(array('ViewScript', array('ViewScript', array('viewScript' => 'forms/groups/edit-group.phtml'))));
            $this->view->form = $form;
            $this->view->color = $model->getColor();
            if ($this->getRequest()->isPost()) {
                if ($this->getRequest()->getPost('submit')) {
                    if ($form->isValid($this->getRequest()->getPost())) {
                        $model->setOptions($form->getValues());
                        if ($groupId = $model->save()) {
                            //$post = $this->getRequest()->getPost('selectedProducts');
//							if ($post!=''){
//								$posts=explode(",",$post);
//								$arrayAll=array();
//								//Needs_Tools::DeleteLegaturibyGroup( $groupId );
//
//								$modelGroupAll=new Default_Model_ProductGroups();
//								$selectAll = $modelGroupAll->getMapper()->getDbTable()->select()
//											->where('idGroup = ?',
//											$groupId);
//								$resultsAll=$modelGroupAll->fetchAll($selectAll);
//								foreach ($resultsAll as $value){
//									$arrayAll[]=$value->getIdProduct();//all the products that are currently in the table	
//								}
//								$resultDiff = array_diff($arrayAll, $posts);
//								foreach ($resultDiff as $deleteProd){
//									Needs_Tools::DeleteLegaturi( $deleteProd );//here we delete the prods that were before in the table, but are not anymore in the new list of prods
//								}
//								foreach ($posts as $key=>$idProductGroups){//all products that need to be in the table
//									$modelGroup=new Default_Model_ProductGroups();
//									$modelGroup->setIdProduct($idProductGroups);
//									$modelGroup->setIdGroup($groupId);
//
//									$modelGroupFind=new Default_Model_ProductGroups();
//									$selectFind = $modelGroupFind->getMapper()->getDbTable()->select()
//											->where('idProduct = ?',
//													$idProductGroups)
//											->where('idGroup = ?',
//											$groupId);
//									$modelGroupFind->fetchRow($selectFind);
//									if ($modelGroupFind->getId() == NULL) //existing product added in the database
//									{
//										$modelGroup->setOrder($key);
//										$modelGroup->save();
//									}
//								}
//							}
                            //mesaj de succes
                            $this->_flashMessenger->addMessage("<div class='success  canhide'><p>Group was modified successfully<a href='javascript:;'></a></p></div>");
                        } else {
                            $this->_flashMessenger->addMessage("<div class='failure canhide'><p>Group was not modified<a href='javascript:;'></a></p></div>");
                        }
                        $this->_redirect(WEBROOT . 'groups');
                    }
                }
            }
        }
    }

    public function deleteAction() {
        $userId = NULL;
        $auth = Zend_Auth::getInstance();
        $authAccount = $auth->getStorage()->read();
        if (null != $authAccount) {
            if (null != $authAccount->getId()) {
                $userId = $authAccount->getId();
            }
        }

        $id = $this->getRequest()->getParam('id');
        $model = new Default_Model_Groups();
        if ($model->find($id)) {
            if ($groupId = $model->delete()) {
                Needs_Tools::DeleteLegaturibyGroup($groupId);
                $this->_flashMessenger->addMessage("<div class='success  canhide'><p>Group was deleted successfully!<a href='javascript:;'></a></p></div>");
            } else {
                $this->_flashMessenger->addMessage("<div class='failure canhide'><p>Group was not deleted!<a href='javascript:;'></a></p></div>");
            }
            $this->_redirect('groups');
        }
    }

}
