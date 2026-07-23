# Mapa de Arquitectura del Proyecto Dino

Este documento describe la arquitectura técnica del proyecto Dino utilizando diagramas de **Mermaid.js**. Estos diagramas ilustran las relaciones entre modelos, el flujo del proceso de Checkout y la comunicación en tiempo real.

---

## 🗺️ Mapa de Relaciones de Modelos (Entity-Relationship)

```mermaid
classDiagram
    class User {
        +int id
        +string name
        +string email
        +string role ("admin" | "vendor" | "delivery")
        +bool is_approved
        +bool is_masivo
        +hasMany products()
        +hasMany supportMessages()
    }

    class Product {
        +int id
        +int user_id
        +string name
        +string slug
        +float price
        +string external_source
        +belongsTo user()
        +hasMany orderItems()
    }

    class Order {
        +int id
        +string status ("pending" | "assigned" | "processing" | "shipped" | "completed")
        +int vendor_id
        +int delivery_user_id
        +bool is_accepted_by_rider
        +float shipping_cost
        +float total
        +belongsTo vendor()
        +belongsTo delivery()
        +hasMany items()
    }

    class OrderItem {
        +int id
        +int order_id
        +int product_id
        +int quantity
        +float price
        +float subtotal
        +belongsTo order()
        +belongsTo product()
    }

    class ShippingZone {
        +int id
        +string zone_key
        +string label
        +float price
        +array coordinates
    }

    class SupportMessage {
        +int id
        +int delivery_user_id
        +int sender_id
        +string message
        +belongsTo delivery()
    }

    User "1" --> "*" Product : owns
    User "1" --> "*" SupportMessage : sends/receives
    Product "1" --> "*" OrderItem : itemized_in
    Order "1" --> "*" OrderItem : contains
    User "1" --> "*" Order : delivers (rider)
    User "1" --> "*" Order : prepares (vendor)
```

---

## ⚡ Ciclo de Vida del Pedido y Transacciones (Checkout)

Flujo secuencial que detalla las interacciones del controlador y los servicios de negocio al procesar un pedido:

```mermaid
sequenceDiagram
    autonumber
    actor Cliente
    participant Cart as CartController
    participant Checkout as CheckoutController
    participant ZoneSrv as ZoneDetectionService
    participant ProcSrv as OrderProcessingService
    participant PaySrv as PaymentService
    participant WASrv as WhatsAppService

    Cliente->>Cart: Agrega productos al carrito
    Cliente->>Checkout: Inicia Checkout (GET /checkout)
    Checkout->>ZoneSrv: Detecta zona de entrega
    Cliente->>Checkout: Confirma Datos (POST /checkout/process)
    Note over Checkout: Procesa validaciones de ProcessCheckoutRequest
    
    rect rgb(240, 240, 240)
        Note over Checkout, ProcSrv: DB Transaction Start
        Checkout->>ProcSrv: createOrder()
        ProcSrv-->>Checkout: Retorna Order Creada
    end

    alt Pago por Transferencia (WhatsApp)
        Checkout->>ProcSrv: finalizeManualOrder()
        Checkout->>WASrv: buildManualPaymentUrl()
        WASrv-->>Checkout: Link de Mensaje WhatsApp
        Checkout-->>Cliente: Redirecciona a Gracias (con botón de WhatsApp)
    else Pago por Mercado Pago
        Checkout->>PaySrv: createPreference()
        PaySrv-->>Checkout: preference_id
        Checkout-->>Cliente: Abre Ventana de Pago MP
    end
```

---

## 🔌 Notificaciones y Websockets (Tiempo Real)

```mermaid
graph TD
    Order[Actualización de Order] -->|Dispara Hook booted/saved| Event[Broadcast Event]
    Event -->|Canal private-vendor.id| Panel[Filament Admin Panel - Orders Table]
    Event -->|Canal private-App.Models.User.id| PWA[PWA del Repartidor - Oferta de Viaje]
    
    Support[Mensaje de Soporte] -->|Event SupportMessageSent| Chat[Chat de Soporte en Tiempo Real]
```
