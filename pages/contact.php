<?php
// Contact page content
?>
<div class="page-content">
    <h1>Contact TechStore Pro</h1>
    
    <div class="about-section">
        <div class="about-text">
            <h2>Get in Touch</h2>
            <p>
                Have questions about our products or need help with your order? Our friendly support team 
                is here to help you find the perfect tech solutions for your needs.
            </p>
            
            <p>
                Whether you're looking for product recommendations, need assistance with returns, or want 
                to learn more about our services, we're just a message away. We typically respond within 
                24 hours during business days.
            </p>
        </div>
        
        <div class="about-image">
            <div class="placeholder-image">
                <span>📞</span>
                <p>Contact Us</p>
            </div>
        </div>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <h3>24h</h3>
            <p>Response Time</p>
        </div>
        <div class="stat-card">
            <h3>4.9/5</h3>
            <p>Support Rating</p>
        </div>
        <div class="stat-card">
            <h3>7 Days</h3>
            <p>Return Policy</p>
        </div>
        <div class="stat-card">
            <h3>24/7</h3>
            <p>Live Chat</p>
        </div>
    </div>
    
    <div class="team-section">
        <h2>Contact Information</h2>
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
            <div class="value-item">
                <h3>📧 General Inquiries</h3>
                <p>For business partnerships, media inquiries, or general questions.</p>
                <p>Email: info@techstorepro.example</p>
            </div>
            <div class="value-item">
                <h3>🔄 Returns & Exchanges</h3>
                <p>Need to return or exchange an item? We make it easy and hassle-free.</p>
                <p>Email: returns@techstorepro.example</p>
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
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
    max-width: 1000px;
    margin: 0 auto;
}

.value-item {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    text-align: center;
    transition: transform 0.3s ease;
    min-height: 180px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.value-item:hover {
    transform: translateY(-5px);
}

.value-item h3 {
    color: #2c3e50;
    margin-bottom: 12px;
    font-size: 1.2em;
}

.value-item p {
    color: #7f8c8d;
    line-height: 1.5;
    margin-bottom: 8px;
    font-size: 0.95em;
}

.value-item p:last-child {
    margin-bottom: 0;
    font-weight: 600;
    color: #3498db;
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
        gap: 20px;
        max-width: 100%;
    }
    
    .value-item {
        min-height: 160px;
        padding: 18px;
    }
    
    .value-item h3 {
        font-size: 1.1em;
        margin-bottom: 10px;
    }
    
    .value-item p {
        font-size: 0.9em;
        margin-bottom: 6px;
    }
}
</style> 