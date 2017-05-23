<?php

namespace model;
class stockDashboard {
    private $stock;
    private $categories;
    private $status;
    private $tickets;

    public function __construct() {
        $this->stock = stock::getAll();
        $this->categories = array();
        $cats = stockCategory::getAll();

        foreach ($cats as $cat) {
            $cats_data = array();
            $cats_data['name'] = $cat->getName();
            $cats_data['items'] = $cat->countstock();
            $cats_data['tickets'] = $cat->countOpenTickets();

            $this->categories[] = $cats_data;
        }

        $this->status = array();
        $stats = stockStatus::getAll();
        foreach ($stats as $stat) {
            $stat_data = array();
            $stat_data['name'] = $stat->getName();
            $stat_data['items'] = $stat->countstock();
            
            $this->status[] = $stat_data;
        }

        $this->tickets = 0;

        foreach ($this->categories as $cat) {
            $ticket_count = $cat['tickets'];
            if (isset($ticket_count)) {
                $this->tickets+=$ticket_count;
            }
        }
    }
    function getstock() {
        return $this->stock;
    }

    function getCategories() {
        return $this->categories;
    }

    function getStatus() {
        return $this->status;
    }

    function getTickets() {
        return $this->tickets;
    }
    function setstock($data) {
        $this->stock = $data;
    }
    function setCategories($data) {
        $this->categories = $data;
    }
    function setStatus($data) {
        $this->status = $data;
    }
    function setTickets($data) {
        $this->tickets = $data;
    }
}
?>
