-- v2.0 Customer Portal + Ticket Verification

CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NULL,
    phone VARCHAR(50) NULL,
    password VARCHAR(255) NULL,
    status ENUM('active','inactive','blocked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_customers_tenant (tenant_id),
    UNIQUE KEY uq_customer_email_tenant (tenant_id, email),
    UNIQUE KEY uq_customer_phone_tenant (tenant_id, phone)
);

CREATE TABLE IF NOT EXISTS customer_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    customer_id INT NULL,
    fiche_id INT NOT NULL,
    verification_code VARCHAR(120) NOT NULL,
    qr_signature VARCHAR(255) NULL,
    status ENUM('active','cancelled','won','lost','claimed','paid') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_customer_tickets_tenant (tenant_id),
    INDEX idx_customer_tickets_customer (customer_id),
    INDEX idx_customer_tickets_fiche (fiche_id),
    UNIQUE KEY uq_customer_ticket_code (verification_code)
);

CREATE TABLE IF NOT EXISTS ticket_claims (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    customer_id INT NULL,
    customer_ticket_id INT NOT NULL,
    fiche_id INT NOT NULL,
    claim_amount DECIMAL(12,2) DEFAULT 0,
    status ENUM('pending','approved','rejected','paid','cancelled') DEFAULT 'pending',
    comment TEXT NULL,
    reviewed_by INT NULL,
    reviewed_at DATETIME NULL,
    paid_by INT NULL,
    paid_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ticket_claims_tenant (tenant_id),
    INDEX idx_ticket_claims_customer_ticket (customer_ticket_id),
    INDEX idx_ticket_claims_status (status)
);
