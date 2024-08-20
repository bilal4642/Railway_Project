<?php
    class Fruit{
        public $name;
        public $color;

        public function __construct($name, $color)
        {
            $this->name = $name;
            $this ->color = $color;
        }
        final public function values(){
            echo "the name of the fruit is {$this ->name} and the color is {$this->color}";
        }
    }

    class NewFruit extends Fruit{
        public $price;
        public function __construct($name, $color, $price)
        {
            $this-> name = $name;
            $this-> color = $color;
            $this-> price = $price;
        }
        public function valuess(){
            echo " the name of the fruit is {$this->name} the color is {$this->color} and the price is {$this->price}" ;
        
    }
}
    $apple = new NewFruit("Apple", "Red", "20");
    $apple->valuess();
    echo "<br>";
    $banana = new NewFruit("Yellow", "Yellow", "45");
    $banana->valuess();

?>