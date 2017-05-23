<?php

namespace model;
class stockCategory extends Entity {

    private $category_id;
    private $description;
    private $notes;
    private $created;
    private $name;
    private $ispublic;
    private $updated;
    private $parent_id;

    public function getJsonProperties() {
        return array(
            'id' => $this->getId(),
            'category_id' => $this->getCategory_id(),
            'description' => $this->getDescription(),
            'notes' => $this->getNotes(),
            'created' => $this->getCreated(),
            'name' => $this->getName(),
            'ispublic' => $this->getIspublic() ? 'Public' : 'Private',
            'updated' => $this->getUpdated(),
            'stock_count' => $this->countstock(),
            'open_ticket_count' => $this->countOpenTickets(),
            'closed_ticket_count' => $this->countClosedTickets()
        );
    }
    function getId() {
        return $this->getCategory_id();
    }

    public function getCategory_id() {
        return $this->category_id;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getNotes() {
        return $this->notes;
    }

    public function getCreated() {
        return $this->created;
    }

    public function getName() {
        return $this->name;
    }

    public function getIspublic() {
        return $this->ispublic;
    }

    public function getUpdated() {
        return $this->updated;
    }

    public function setCategory_id($category_id) {
        $this->category_id = $category_id;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setNotes($notes) {
        $this->notes = $notes;
    }

    public function setCreated($created) {
        $this->created = $created;
    }
    public function setName($name) {
        $this->name = $name;
    }

    public function setIspublic($ispublic) {
        $this->ispublic = $ispublic;
    }

    public function setUpdated($updated) {
        $this->updated = $updated;
    }
    public function getOpenTicketCount() {
        return $this->open_ticket_count;
    }

    public function getClosedTicketCount() {
        return $this->closed_ticket_count;
    }

    public function getParent_id() {
        return $this->parent_id;
    }

    public function setParent_id($parent_id) {
        $this->parent_id = $parent_id;
    }
    public function countOpenTickets() {
        $sql = 'SELECT COUNT(ticket_id) FROM ' . stock_TICKET_VIEW . ' '
                . 'WHERE category_id=' . db_input($this->getId()) . ' '
                . 'AND status="open"';
        list($count) = db_fetch_row(db_query($sql));
        return $count;
    }
    public static function getTicketList($tickets_status, $category_id) {
        $ticket_ids = array();
        $sql = 'SELECT ticket_id, stock_id from ' . stock_TICKET_VIEW . ' '
                . 'where `status`="' . $tickets_status . '"'
                . ' AND category_id=' . $category_id;

        $res = db_query($sql);
        if ($res && ($num = db_num_rows($res))) {
            while ($row = db_fetch_array($res)) {
                $ticket_ids[] = $row;
            }
        }

        return $ticket_ids;
    }

    public function countClosedTickets() {
        $sql = 'SELECT COUNT(ticket_id) FROM ' . stock_TICKET_VIEW . ' '
                . 'WHERE category_id=' . db_input($this->getId()) . ' '
                . 'AND status="closed"';
        list($count) = db_fetch_row(db_query($sql));
        return $count;
    }
    public static function countAll() {
        return db_count('SELECT count(*) FROM ' . stock_CATEGORY_TABLE . ' cat ');
    }
    public function countstock() {
        $count = count($this->getstockIds());
        $kids = $this->getChildren();
        foreach($kids as $kid)
        {
            $count += $kid->countstock();
        }
        return $count;
    }
    private function getstockIds() {
        $ids = array();
        $sql = ' SELECT stock.stock_id as stock_id'
                . ' FROM ' . stock_CATEGORY_TABLE . ' cat '
                . ' LEFT JOIN ' . stock_TABLE . ' stock ON(stock.category_id=cat.category_id) '
                . ' WHERE cat.category_id=' . db_input($this->getId())
        ;
        $res = db_query($sql);
        if ($res && ($num = db_num_rows($res))) {
            while ($row = db_fetch_array($res)) {
                $id = $row['stock_id'];
                if (isset($id) && $id > 0) {
                    $ids[] = $id;
                }
            }
        }
        return $ids;
    }
    public function getChildren() {
        $categories = array();
        $sql = ' SELECT category_id as category_id'
                . ' FROM ' . stock_CATEGORY_TABLE
                . ' WHERE parent_id =' . db_input($this->getId());

        $res = db_query($sql);
        if ($res && ($num = db_num_rows($res))) {
            while ($row = db_fetch_array($res)) {
                $id = $row['category_id'];
                if (isset($id) && $id > 0) {
                    $category = new \model\stockCategory($id);
                    $categories[] = $category;
                }
            }
        }

        return $categories;
    }
    public function getstock() {

        $ids = $this->getstockIds();
        $stock = array();
        foreach ($ids as $id) {
            $item = new stock($id);
            $stock[] = $item;
        }

        return $stock;
    }
    protected function getSaveSQL() {
        $created = $this->category_id > 0 ? $this->created : 'NOW()';
        $sql = 'description=' . db_input($this->getDescription()) .
                ',notes=' . db_input($this->getNotes()) .
                ',name=' . db_input($this->getName()) .
                ',ispublic=' . $this->getIspublic() .
                ',updated= NOW()' .
                ',created=' . $created .
                ',parent_id=' . db_input($this->getParent_id());
        return $sql;
    }
    protected function init() {
        $this->category_id = 0;
        $this->description = '';
        $this->notes = '';
        $this->created = null;
        $this->name = '';
        $this->ispublic = 0;
        $this->updated = null;
        $this->parent_id = 0;
    }

    public function validate() {
        $retval = isset($this->description);
        if (!$retval) {
            $this->addError('Invalid Description!');
        }
        return $retval;
    }
    protected function setId($id) {
        $this->setCategory_id($id);
    }
    protected static function getIdColumn() {
        return 'category_id';
    }
    protected static function getTableName() {
        return stock_CATEGORY_TABLE;
    }
    public function expose() {
        return json_encode($this);
    }
}
?>
