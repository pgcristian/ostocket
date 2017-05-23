<?php


namespace controller;

class stockCategory extends Controller {

    protected function getEntityClassName() {
        return 'model\stockCategory';
    }

    protected function getViewDirectory() {
        return 'category';
    }

    protected function getTitle($plural = true) {
        return $plural ? 'stock Categories' : 'stock Category';
    }

    protected function getListColumns() {
        return array(
            array('field' => 'name', 'headerText' => 'Name', 'sortable' => 'true'),
            array('field' => 'ispublic', 'headerText' => 'Type', 'sortable' => 'true'),
            array('field' => 'stock_count', 'headerText' => 'stock', 'sortable' => 'true'),
            array('field' => 'open_ticket_count', 'headerText' => 'Open Tickets',
                'sortable' => 'true'),
            array('field' => 'closed_ticket_count', 'headerText' => 'Closed Tickets',
                'sortable' => 'true'),
            array('field' => 'updated', 'headerText' => 'Last Updated', 'sortable' => 'true')
        );
    }

    public function listJsonTreeAction() {
        $entityClass = $this->getEntityClassName();
        $items = $entityClass::getAll();
        $object = array();
        foreach ($items as $item) {
            if($item->getParent_id() == 0)
            {
                $data = $this->getJsonTreeObject($item);    
                $object[] = $data;
            }
        }

        return json_encode($object);
    }

    private function getJsonTreeObject($item) {
        $data = array();
        $data['label'] = null;
        $data['data'] = $item->getJsonProperties();
        $data['leaf'] = false;
        $children=$item->getChildren();
        $kids=array();
        
        foreach($children as $child)
        {
            $kids[] = $this->getJsonTreeObject($child);
        }
        $data['children'] = $kids;
        return $data;
    }

    public function categoryItemsJsonAction($category_id) {
        $category = new \model\stockCategory($category_id);
        $stock = $category->getstock();
        $items = array();

        foreach ($stock as $item) {
            $status = $item->getStatus();
            $item_data = array(
                'id' => $item->getId(),
                'asset_id' => $item->getAsset_id(),
                'category' => $category->getName(),
                'status' => isset($status) ? $status->getName() : '',
                'published' => $item->getIspublished() ? 'Yes' : 'No',
                'active' => $item->getIs_active() ? 'Yes' : 'No'
            );
            $items[] = $item_data;
        }
        echo json_encode($items);
    }

    protected function getListTemplateName() {
        return 'listTreeTemplate.html.twig';
    }

}
