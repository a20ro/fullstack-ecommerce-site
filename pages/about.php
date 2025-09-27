<?php
// About page content
?>
<div class="page-content">
    <h1>About TechStore Pro</h1>
    
    <div class="about-section">
        <div class="about-text">
            <h2>Our Story</h2>
            <p>
                TechStore Pro was founded with a clear mission: make premium tech accessible to everyone.
                From flagship smartphones and powerful laptops to everyday accessories, we curate products
                that deliver performance, reliability, and great value.
            </p>
            
            <p>
                We partner with trusted brands and vetted suppliers to ensure authentic products, competitive
                pricing, and fast shipping. Our team of tech enthusiasts hand-picks each item so you can shop
                with confidence.
            </p>
        </div>
        
        <div class="about-image">
            <div class="placeholder-image">
                <span>🛍️</span>
                <p>Store Highlights</p>
            </div>
        </div>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <h3>1,500+</h3>
            <p>Products</p>
        </div>
        <div class="stat-card">
            <h3>4.8/5</h3>
            <p>Customer Rating</p>
        </div>
        <div class="stat-card">
            <h3>48h</h3>
            <p>Avg. Shipping</p>
        </div>
        <div class="stat-card">
            <h3>24/7</h3>
            <p>Support</p>
        </div>
    </div>
    
    <div class="team-section">
        <h2>Our Values</h2>
        <div class="values-grid">
            <div class="value-item">
                <h3>✅ Quality</h3>
                <p>We offer authentic, high‑quality products backed by warranty and support.</p>
            </div>
            <div class="value-item">
                <h3>💬 Customer First</h3>
                <p>Friendly support, easy returns, and clear communication—always.</p>
            </div>
            <div class="value-item">
                <h3>🚚 Fast Delivery</h3>
                <p>We ship quickly with real‑time tracking so you get gear on time.</p>
            </div>
            <div class="value-item">
                <h3>🔒 Secure Payments</h3>
                <p>Your data and transactions are protected with industry‑standard security.</p>
            </div>
        </div>
    </div>

    <div class="team-section">
        <h2>Get in Touch</h2>
        <div class="values-grid">
            <div class="value-item">
                <h3>📞 Support</h3>
                <p>Have a question about a product or an order? Our team is here to help.</p>
                <p>Email: support@techstorepro.example</p>
            </div>
            <div class="value-item">
                <h3>🏪 Store Info</h3>
                <p>Open: Mon–Sat, 9:00 AM – 6:00 PM</p>
                <p>Location: Online-first with regional warehouses</p>
            </div>
        </div>
    </div>
</div>

<style>
.about-section {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 40px;
    margin: 30px 0;
    align-items: start;
}

.about-text h2 {
    color: #2c3e50;
    margin-bottom: 20px;
    font-size: 1.8em;
}

.about-text p {
    margin-bottom: 20px;
    line-height: 1.8;
    font-size: 1.1em;
}

.about-image {
    display: flex;
    justify-content: center;
    align-items: center;
}

.placeholder-image {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: white;
    padding: 40px;
    border-radius: 10px;
    text-align: center;
    width: 100%;
    max-width: 200px;
}

.placeholder-image span {
    font-size: 3em;
    display: block;
    margin-bottom: 10px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 20px;
    margin: 40px 0;
}

.stat-card {
    background: white;
    padding: 30px 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    text-align: center;
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-card h3 {
    color: #3498db;
    font-size: 2.5em;
    margin-bottom: 10px;
    font-weight: bold;
}

.stat-card p {
    color: #7f8c8d;
    font-weight: bold;
}

.team-section {
    margin: 40px 0;
}

.team-section h2 {
    color: #2c3e50;
    margin-bottom: 30px;
    font-size: 1.8em;
    text-align: center;
}

.values-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
}

.value-item {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    text-align: center;
    transition: transform 0.3s ease;
}

.value-item:hover {
    transform: translateY(-5px);
}

.value-item h3 {
    color: #2c3e50;
    margin-bottom: 15px;
    font-size: 1.3em;
}

.value-item p {
    color: #7f8c8d;
    line-height: 1.6;
}

@media (max-width: 768px) {
    .about-section {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .values-grid {
        grid-template-columns: 1fr;
    }
}
</style> 
</style> 