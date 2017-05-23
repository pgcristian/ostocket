<?php
namespace controller;

class stockStatus extends Controller {

    protected function getEntityClassName() {
        return 'model\stockStatus';
    }

    public function statusItemsJsonAction($status_id) {
        $stock = stock_Status::getstock($status_id);
        $items = array();

        foreach ($stock as $item) {
            if ($item->getId() > 0) {
                $item_data = array(
                    'id' => $item->getId(),
                    'asset_id' => $item->getAssetId(),
                    'category' => $item->getCategory()->getName(),
                    'status' => $item->getStatus()->getName(),
                    'published' => $item->isPublished() ? 'Yes' : 'No',
                    'active' => $item->isActive() ? 'Yes' : 'No'
                );
                $items[] = $item_data;
            }
        }
        echo json_encode($items);
    }

    protected function getListColumns() {
        return array(
            array('field' => 'name', 'headerText' => 'Name', 'sortable' => 'true'),
            array('field' => 'color', 'headerText' => 'Color', 'sortable' => 'true'),
            array('field' => 'image', 'headerText' => 'Image', 'sortable' => 'true'),
            array('field' => 'stocks', 'headerText' => 'stock', 'sortable' => 'true'),
            array('field' => 'baseline', 'headerText' => 'Is Default?', 'sortable' => 'true')
        );
    }

    protected function getTitle($plural = true) {
        return 'stock Status';
    }

    protected function getViewDirectory() {
        return 'status';
    }

}
