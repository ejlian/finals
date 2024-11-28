<?php
abstract class DeliveryMode {
    protected $orderId;
    protected $baseRate;
    protected $estimatedTime;
    
    public function __construct($orderId) {
        $this->orderId = $orderId;
    }

    abstract public function calculateDeliveryFee(): float;
    abstract public function getEstimatedTime(): int;
}

class StandardDelivery extends DeliveryMode {
    public function __construct($orderId) {
        parent::__construct($orderId);
        $this->baseRate = 5.00;
        $this->estimatedTime = 60;
    }

    public function calculateDeliveryFee(): float {
        return $this->baseRate;
    }

    public function getEstimatedTime(): int {
        return $this->estimatedTime;
    }
}

class ExpressDelivery extends DeliveryMode {
    public function __construct($orderId) {
        parent::__construct($orderId);
        $this->baseRate = 15.00;
        $this->estimatedTime = 30;
    }

    public function calculateDeliveryFee(): float {
        return $this->baseRate;
    }

    public function getEstimatedTime(): int {
        return $this->estimatedTime;
    }
}

class BikeDelivery extends DeliveryMode {
    public function __construct($orderId) {
        parent::__construct($orderId);
        $this->baseRate = 8.00;
        $this->estimatedTime = 45;
    }

    public function calculateDeliveryFee(): float {
        return $this->baseRate;
    }

    public function getEstimatedTime(): int {
        return $this->estimatedTime;
    }
}
?>