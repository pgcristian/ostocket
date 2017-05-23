<?php
namespace model;
class TicketRecurring extends Entity {
    private $id;
    private $stock_id;
    private $ticket_id;
    private $last_opened;
    private $interval;
    private $active;
    private $interval_multiplier;
    private $next_date;
    public function getJsonProperties() {
        return array(
            'id' => $this->getId(),
            'stock' => $this->getstock()->getAsset_id(),
            'stock_id' => $this->getstock_id(),
            'ticket' => $this->getTicket()->getNumber(),
            'ticket_id' => $this->getTicket_id(),
            'subject' => $this->getTicket()->getSubject(),
            'last_opened' => $this->getLast_opened(),
            'interval' => $this->getInterval(),
            'hr_interval' => $this->getHRInterval(),
            'active' => $this->getActive()>0 ? 'Yes' : 'No',
        );
    }

    protected function validate() {
        $retval = isset($this->stock_id) && $this->stock_id > 0;
        if (!$retval) {
            $this->addError('Invalid stock ID!');
            return $retval;
        }

        $retval = isset($this->ticket_id) && $this->ticket_id > 0;
        if (!$retval) {
            $this->addError('Invalid Ticket ID!');
            return $retval;
        }

        $retval = isset($this->interval) && $this->interval > 0;
        if (!$retval) {
            $this->addError('Invalid Inverval!');
            return $retval;
        }
        return $retval;
    }
    public function getId() {
        return $this->id;
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
            return \Ticket::lookup($this->ticket_id);
        }
        return null;
    }

    public function getLast_opened() {
        return $this->last_opened;
    }

    public function getNext_date() {
        return $this->next_date;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setstock_id($stock_id) {
        $this->stock_id = $stock_id;
    }

    public function setTicket_id($ticket_id) {
        $this->ticket_id = $ticket_id;
    }

    public function setLast_opened($last_opened) {
        $this->last_opened = $last_opened;
    }

    public function setNext_date($next_date) {
        $this->next_date = $next_date;
    }

    public function getInterval() {
        return $this->interval;
    }

    public function setInterval($interval) {
        $this->interval = $interval;
        $this->getHRInterval();
    }

    public function getActive() {
        return $this->active;
    }

    public function setActive($active) {
        if(is_numeric($active))
        {
            $this->active = $active;
        }
        else
        {
            $this->active = ($active == 'on')?1:0;
        }
    }

    public function getInterval_multiplier() {
        return $this->interval_multiplier;
    }

    public function setInterval_multiplier($interval_multiplier) {
        $this->interval_multiplier = $interval_multiplier;
    }
    
    public function getHRInterval()
    {
        $seconds = intval($this->interval);
        if($seconds > 0)
        {
            $mod = $seconds % 86400;
            if($mod == 0)
            {
                $this->setInterval_multiplier(86400);
                return ($seconds / 86400).' Days';
            }
            
            $mod = $seconds % 3600;
            if($mod == 0)
            {
                $this->setInterval_multiplier(3600);
                return ($seconds / 3600).' Hours';
            }
            
            $mod = $seconds % 60;
            if($mod == 0)
            {
                $this->setInterval_multiplier(60);
                return ($seconds / 60).' Minutes';
            }
        }
        $this->setInterval_multiplier(1);
        return $seconds.' Seconds';
    }
    public static function findBystockId($id) {
        $items = array();
        $sql = 'SELECT id ' .
                ' FROM ' . stock_TICKET_RECURRING__TABLE .
                ' WHERE stock_id=' . db_input($id);

        return static::populateBySQL($sql);
    }

    public static function findByTicketId($id) {
        $items = array();
        $sql = 'SELECT id ' .
                ' FROM ' . stock_TICKET_RECURRING__TABLE .
                ' WHERE ticket_id=' . db_input($id);

        return static::populateBySQL($sql);
    }
    protected function getSaveSQL() {

        $seconds = intval($this->interval);
        $multiplier = intval($this->interval_multiplier);
        $seconds = $seconds * $multiplier;

        $date = strtotime($this->next_date);
        $db_date = date('Y-m-d H:i:s',
                $date);

        $sql = 'stock_id=' . db_input($this->stock_id) .
                ',ticket_id=' . db_input($this->ticket_id) .
                ',last_opened=' . db_input($this->last_opened) .
                ',next_date=' . db_input($db_date) .
                ',`interval`=' . db_input($seconds) .
                ',active=' . db_input($this->active);
        return $sql;
    }
    protected function init() {
        $this->id = 0;
        $this->stock_id = 0;
        $this->ticket_id = 0;
        $this->last_opened = null;
        $this->next_date = null;
        $this->interval = 0;
        $this->active = 0;
        $this->interval_multiplier = 1;
    }

    protected static function getIdColumn() {
        return 'id';
    }

    protected static function getTableName() {
        return stock_TICKET_RECURRING__TABLE;
    }
}
