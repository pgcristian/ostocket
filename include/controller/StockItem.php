<?php

namespace controller;

require_once(INCLUDE_DIR . 'class.dynamic_forms.php');

class stockItem extends Controller {

    protected function getEntityClassName() {
        return 'model\stock';
    }

    protected function getListTemplateName() {
        return 'listItemTemplate.html.twig';
    }

    protected function getListColumns() {
        return array(
            array('field' => 'asset_id', 'headerText' => 'Asset ID', 'sortable' => 'true'),
            array('field' => 'category', 'headerText' => 'Category', 'sortable' => 'true'),
            array('field' => 'status', 'headerText' => 'Status', 'sortable' => 'true'),
            array('field' => 'ispublished', 'headerText' => 'Is Published', 'sortable' => 'true'),
            array('field' => 'is_active', 'headerText' => 'Is Active?', 'sortable' => 'true'),
            array('field' => 'staff', 'headerText' => 'Assigned To', 'sortable' => 'true')
        );
    }

    protected function getTitle($plural = true) {
        return $plural ? 'stock Items' : 'stock Item';
    }

    public function getDynamicForm($id = 0) {
        $form_id = \stockPlugin::getCustomForm();
        if (isset($form_id)) {
            $form = \DynamicForm::lookup($form_id);
            if ($id > 0) {
                $data = \model\stock::getDynamicData($id);
                $one = $data->one();
                if (isset($one)) {
                    $one->getSaved();
                    return $one->getForm()->render(true);
                }
            }
            if (isset($form)) {
                return $form->getForm()->render(true);
            }
        }
    }

    public function saveAction() {
        $form_id = \stockPlugin::getCustomForm();
        if (isset($form_id)) {
            $_POST['form_id'] = $form_id;
        }
        return parent::saveAction();
    }

    protected function getViewDirectory() {
        return 'item';
    }

    public function publishAction() {
        $id = $_POST['item_id'];
        if (isset($id) && $id > 0) {
            $item = new \model\stock($id);
            if (isset($item)) {
                $item->setIspublished($_POST['item_publish']);
                $item->save();
            }
        }
    }

    public function activateAction() {
        $id = $_POST['item_id'];
        if (isset($id) && $id > 0) {
            $item = new \model\stock($id);
            if (isset($item)) {
                $item->setIs_active($_POST['item_activate']);
                $item->save();
            }
        }
    }

    public function searchAction() {
        $properties = array();
        $needle = $_POST['searchCriteria'];

        if (isset($needle)) {
            $items = \model\stock::search($needle);
            foreach ($items as $item) {
                $properties[] = $item->getJsonProperties();
            }
        }

        $args = array();
        $args['title'] = count($properties) > 0 ? 'Search Results:' : 'Nothing Found!';
        $args['dt_columns'] = $this->getListColumns();
        $args['data'] = json_encode($properties);

        $template_name = parent::getListTemplateName();
        $this->render($template_name, $args);
    }

    public function listStaffJsonAction() {
        $staff = array();
        $items = \Staff::getAvailableStaffMembers();
        foreach ($items as $id => $name) {
            if (isset($id) && isset($name)) {
                $entry = array();
                $entry['name'] = $name;
                $entry['staff_id'] = $id;
                $staff[] = $entry;
            }
        }
        return json_encode($staff);
    }

    public function listBelongingJsonAction() {
        $properties = array();
        $staff = \StaffAuthenticationBackend::getUser();
        if (isset($staff)) {
            $items = \model\stock::findByStaffId($staff->getId());
        }
        foreach ($items as $item) {
            $properties[] = $item->getJsonProperties();
        }
        echo json_encode($properties);
    }

    public function listNotBelongingJsonAction() {
        $properties = array();
        $staff = \StaffAuthenticationBackend::getUser();
        if (isset($staff)) {
            $items = \model\stock::findByNotStaffId($staff->getId());
        }
        foreach ($items as $item) {
            $properties[] = $item->getJsonProperties();
        }
        echo json_encode($properties);
    }

    public function openNewTicketAction() {
        $id = $_POST['id'];
        if (isset($id)) {
            $item = new \model\stock($id);
            if (isset($item)) {
                $form_id = $item->getTicketFormId();
                $form = \DynamicForm::lookup($form_id);
                if (isset($form)) {
                    $data_id = $form->getField('asset_id')->getWidget()->name;
                    $_SESSION[':form-data'] = array($data_id => $item->getAsset_id());
                    header("Location: ".OST_WEB_ROOT."scp/tickets.php?a=open");
                    die();
                }
            }
        }
    }

}
