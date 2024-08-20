<?php
    class Fruit{
        public $name;
        public $color;

        public function __construct($name, $color)
        {
            $this->name = $name;
            $this ->color = $color;
        }
        protected function values(){
            echo "the name of the fruit is {$this ->name} and the color is {$this->color}";
        }
    }

    class Orange extends Fruit{
        public function show(){
            echo "I am from fruit class";
            $this->values();
        }
    }
    $apple = new Orange("Apple", "Red");
    $apple->show();

?>