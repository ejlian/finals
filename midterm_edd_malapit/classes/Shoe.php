<?php
class Shoe {
    private $id;
    private $name;
    private $price;
    private $size;

    public function __construct($id, $name, $price, $size) {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->size = $size;
    }

    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getPrice() { return $this->price; }
    public function getSize() { return $this->size; }
}
?>