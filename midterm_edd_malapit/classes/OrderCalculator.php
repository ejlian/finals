<?php

class OrderCalculator {
    private $subtotal = 0;
    private $deliveryFee = 0;
    private $tax = 0;
    
    public function calculateTotal($cartItems, $deliveryMethod) {
        // Calculate subtotal
        $this->subtotal = array_reduce($cartItems, function($sum, $item) {
            return $sum + ($item['price'] * $item['quantity']);
        }, 0);
        
        // Calculate delivery fee
        $this->deliveryFee = $this->calculateDeliveryFee($deliveryMethod);
        
        // Calculate tax if applicable
        $this->tax = $this->calculateTax($this->subtotal);
        
        return [
            'subtotal' => $this->subtotal,
            'delivery_fee' => $this->deliveryFee,
            'tax' => $this->tax,
            'total' => $this->subtotal + $this->deliveryFee + $this->tax
        ];
    }
    
    private function calculateDeliveryFee($method) {
        return match($method) {
            'standard' => 5.00,
            'express' => 15.00,
            'pickup' => 0.00,
            default => 0.00
        };
    }
    
    private function calculateTax($amount) {
        // Add your tax calculation logic here
        return 0;
    }
} 