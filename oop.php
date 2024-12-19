<?php

// ----------------------------------------
// ENCAPSULATION
// ----------------------------------------
class Car {
    private $brand;
    private $speed;
    private $fuelLevel;

    public function __construct($brand, $fuelLevel = 100) {
        $this->brand = $brand;
        $this->fuelLevel = $fuelLevel;
        $this->speed = 0; // Default speed is 0
    }

    public function getBrand() {
        return $this->brand;
    }

    public function getSpeed() {
        return $this->speed;
    }

    public function accelerate($amount) {
        if ($this->fuelLevel > 0) {
            $this->speed += $amount;
            $this->fuelLevel -= $amount / 2; // Consumes fuel as speed increases
        } else {
            echo "Not enough fuel to accelerate.\n";
        }
    }

    public function refuel($amount) {
        $this->fuelLevel += $amount;
        echo "Car refueled. Current fuel level: {$this->fuelLevel}.\n";
    }
}

// Usage
$car = new Car("Toyota");
echo "Brand: " . $car->getBrand() . "\n"; // Toyota
$car->accelerate(20);
echo "Speed: " . $car->getSpeed() . "\n"; // 20
$car->refuel(30);




// ----------------------------------------------
// INHERITANCE
// ----------------------------------------------
class ElectricCar extends Car {
    private $batteryLevel;

    public function __construct($brand, $batteryLevel = 100) {
        parent::__construct($brand, 0); // No fuel for electric cars
        $this->batteryLevel = $batteryLevel;
    }

    public function chargeBattery($amount) {
        $this->batteryLevel += $amount;
        echo "Battery charged to {$this->batteryLevel}%.\n";
    }

    public function accelerate($amount) {
        if ($this->batteryLevel > 0) {
            parent::accelerate($amount); // Use parent class's method for speed logic
            $this->batteryLevel -= $amount / 3; // Consumes battery instead of fuel
        } else {
            echo "Not enough battery to accelerate.\n";
        }
    }
}

// Usage
$electricCar = new ElectricCar("Tesla");
$electricCar->accelerate(30);
$electricCar->chargeBattery(20);




// -------------------------------------
// POLYMORPHISM
// -------------------------------------
class GasCar extends Car {
    public function refuel($amount) {
        parent::refuel($amount);
        echo "Gas car refueled with {$amount} liters of gas.\n";
    }
}

class ElectricCar extends Car {
    public function refuel($amount) {
        echo "Electric cars do not use fuel. Use chargeBattery() instead.\n";
    }
}

// Usage
$gasCar = new GasCar("Ford");
$electricCar = new ElectricCar("Tesla");

$gasCar->refuel(50); // Gas car refueled with 50 liters of gas.
$electricCar->refuel(50); // Electric cars do not use fuel. Use chargeBattery() instead.





// -------------------------------
// ABSTRACTION
// -------------------------------
abstract class Car {
    protected $brand;

    public function __construct($brand) {
        $this->brand = $brand;
    }

    abstract public function drive(); // Abstract method: must be implemented by subclasses

    public function getBrand() {
        return $this->brand;
    }
}

class GasCar extends Car {
    public function drive() {
        echo "{$this->brand} is driving using gasoline.\n";
    }
}

class ElectricCar extends Car {
    public function drive() {
        echo "{$this->brand} is driving using electricity.\n";
    }
}

// Usage
$gasCar = new GasCar("Toyota");
$electricCar = new ElectricCar("Tesla");

$gasCar->drive(); // Toyota is driving using gasoline.
$electricCar->drive(); // Tesla is driving using electricity.

?>