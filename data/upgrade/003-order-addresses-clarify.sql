UPDATE ecommerce_orders SET shipping_address_id = NULL WHERE ecommerce_shipment_id IS NOT NULL;
UPDATE ecommerce_orders SET billing_address_id = NULL WHERE billing_invoice_id IS NOT NULL;