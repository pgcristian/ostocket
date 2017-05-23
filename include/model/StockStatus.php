<?php
namespace model;
class stockStatus extends Entity {

    private $status_id;
    private $name;
    private $image;
    private $description;
    private $color;
    private $baseline;
    
    public function getJsonProperties() {
        return array(
            'id' => $this->getId(),
            'status_id' => $this->getStatus_id(),
            'name' => $this->getName(),
            'image' => $this->getImage(),
            'description' => $this->getDescription(),
            'color' => $this->getColor(),
            'baseline' => $this->getBaseline()?'Yes':'No',
            'stocks' => $this->countstock()
        );
    }

    public function getId() {
        return $this->getStatus_id();
    }

    public function getStatus_id() {
        return $this->status_id;
    }

    public function getName() {
        return $this->name;
    }

    public function getImage() {
        return $this->image;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getColor() {
        return $this->color;
    }

    public function getBaseline() {
        return $this->baseline;
    }

    public function setStatus_id($status_id) {
        $this->status_id = $status_id;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setImage($image) {
        $this->image = $image;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setColor($color) {
        $this->color = $color;
    }

    public function setBaseline($baseline) {
        $this->baseline = $baseline;
    }
    public static function getBaselineStatus() {
        $sql = 'SELECT status_id '
                . ' FROM ' . stock_STATUS_TABLE;
        $sql.=' WHERE baseline=1';
        $sql.=' ORDER BY name';
        $sql.=' LIMIT 1';

        list($id) = db_fetch_row(db_query($sql));

        if ($id) {
            return new stockStatus($id);
        }

        return false;
    }

    public function save() {
        if ($this->getId() > 0) {
            $presql = 'UPDATE ' . LIST_ITEM_TABLE . ' SET value=' . db_input($this->getName())
                    . 'WHERE properties=' . db_input($this->getId())
                    . ' LIMIT 1';
            db_query($presql);
        }
        return parent::save();
    }

    public function getstock() {
        $ids = $this->getstockIds();
        $stock = array();
        foreach ($ids as $id) {
            $item = new stock($id['stock_id']);
            $stock[] = $item;
        }

        return $stock;
    }
    
     private function getstockIds() {
        $ids = array();
        $sql = ' SELECT stock.stock_id as stock_id '
                . ' FROM ' . stock_STATUS_TABLE . ' status '
                . ' LEFT JOIN ' . stock_TABLE . ' stock ON(stock.status_id=status.status_id) '
                . ' WHERE status.status_id=' . db_input($this->getId())
        ;
        $res = db_query($sql);
        if ($res && ($num = db_num_rows($res))) {
            while ($row = db_fetch_array($res)) {
                $id = $row['stock_id'];
                if(isset($id) && $id >0)
                {
                    $ids[] = $id;
                }
            }
        }

        return $ids;
    }
   
    public function countstock() {
        return count($this->getstockIds());
    }
    
     public function countstockByCategory($category_id) {
        $ids = array();
        $sql = ' SELECT stock.stock_id as stock_id '
                . ' FROM ' . stock_STATUS_TABLE . ' status '
                . ' LEFT JOIN ' . stock_TABLE . ' stock ON(stock.status_id=status.status_id) '
                . ' WHERE status.status_id=' . db_input($this->getId())
                . ' AND stock.category_id=' . db_input($category_id)
        ;
        $res = db_query($sql);
        if ($res && ($num = db_num_rows($res))) {
            while ($row = db_fetch_array($res)) {
                $id = $row['stock_id'];
                if(isset($id) && $id >0)
                {
                    $ids[] = $id;
                }
            }
        }

        return count($ids);
    }
    protected function getSaveSQL() {
        $sql = 'name=' . db_input($this->name) .
                ',image=' . db_input($this->image) .
                ',description=' . db_input($this->description) .
                ',color=' . db_input($this->color) .
                ',baseline=' . db_input($this->baseline);
        return $sql;
    }

    protected function init() {
        $this->status_id = 0;
        $this->name = '';
        $this->image = '';
        $this->description = '';
        $this->color = '';
        $this->baseline = 0;
    }

    protected function setId($id) {
        $this->setStatus_id($id);
    }

    protected static function getIdColumn() {
        return "status_id";
    }

    protected static function getTableName() {
        return stock_STATUS_TABLE;
    }

    protected function validate() {
        $retval = isset($this->name);
        if (!$retval) {
            $this->addError('Invalid Name!');
            return $retval;
        }

        return $retval;
    }

}

?>
