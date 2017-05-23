<?php
namespace model;
class stockTicket extends Entity {
    private $stock_id;
    private $ticket_id;
    private $created;
    protected function getId() {  
    }
    protected function getSaveSQL() { 
    }
    protected function init() {  
    }
    protected function setId($id) {
        $this->stock_id = 0;
        $this->ticket_id = 0;
        $this->created = null;
    }
    protected static function getIdColumn() {
        
    }
    protected static function getTableName() {
        return stock_TICKET_TABLE;
    }
    public function getJsonProperties() {
        
    }
    protected function validate() {
        return false;
    }
    public function getstock_id() {
        return $this->stock_id;
    }

    public function getstock() {
        if ($this->stock_id > 0) {
            return new stock($this->stock_id);
        }
        return null;
    }

    public function getTicket_id() {
        return $this->ticket_id;
    }

    public function getTicket() {
        if ($this->ticket_id > 0) {
            return Ticket::lookup($this->ticket_id);
        }
        return null;
    }

    public function getCreated() {
        return $this->created;
    }
    public static function getAllstock() {
        $items = array();
        $sql = 'SELECT DISTINCT(stock_id) as stock_id' .
                ' FROM ' . stock_TICKET_TABLE;

        $res = db_query($sql);
        if ($res && ($num = db_num_rows($res))) {
            while ($row = db_fetch_array($res)) {
                $item = new stock($row['stock_id']);
                if (isset($item)) {
                    $items[] = $item;
                }
            }
        }
        return $items;
    }
    public static function getAllTickets() {
        $items = array();
        $sql = 'SELECT DISTINCT(ticket_id) as ticket_id ' .
                ' FROM ' . stock_TICKET_TABLE;

        $res = db_query($sql);
        if ($res && ($num = db_num_rows($res))) {
            while ($row = db_fetch_array($res)) {
                $item = \Ticket::lookup($row['ticket_id']);
                if (isset($item)) {
                    $items[] = $item;
                }
            }
        }
        return $items;
    }
    public static function findBystockId($id) {
        $items = array();
        $sql = 'SELECT * ' .
                ' FROM ' . stock_TICKET_TABLE .
                ' WHERE stock_id=' . db_input($id);

        $res = db_query($sql);
        if ($res && ($num = db_num_rows($res))) {
            while ($row = db_fetch_array($res)) {
                $item = new stockTicket($row['ticket_id'],
                        $row['stock_id']);
                if (isset($item)) {
                    $items[] = $item;
                }
            }
        }

        return $items;
    }
    public static function findByTicketId($id) {
        $items = array();
        $sql = 'SELECT * ' .
                ' FROM ' . stock_TICKET_TABLE .
                ' WHERE ticket_id=' . db_input($id);

        $res = db_query($sql);
        if ($res && ($num = db_num_rows($res))) {
            while ($row = db_fetch_array($res)) {
                $item = new stockTicket($row['ticket_id'],
                        $row['stock_id']);
                if (isset($item)) {
                    $items[] = $item;
                }
            }
        }
        return $items;
    }
}
