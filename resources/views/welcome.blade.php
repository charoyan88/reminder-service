<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reminder Service</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background-color: #4A90E2;
            color: white;
            padding: 40px 0;
            text-align: center;
            margin-bottom: 40px;
        }
        
        h1 {
            font-size: 36px;
            margin-bottom: 15px;
        }
        
        .tagline {
            font-size: 18px;
            margin-bottom: 30px;
        }
        
        .cta-button {
            display: inline-block;
            background-color: #FF6B6B;
            color: white;
            padding: 12px 30px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        
        .cta-button:hover {
            background-color: #FF5252;
        }
        
        .features {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            margin-bottom: 60px;
        }
        
        .feature {
            flex: 1;
            min-width: 300px;
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        }
        
        .feature h3 {
            color: #4A90E2;
            margin-top: 0;
        }
        
        .how-it-works {
            background-color: #f0f7ff;
            padding: 60px 0;
            margin-bottom: 60px;
        }
        
        .steps {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            counter-reset: step-counter;
        }
        
        .step {
            flex: 1;
            min-width: 200px;
            position: relative;
            padding-left: 70px;
            padding-bottom: 20px;
        }
        
        .step:before {
            counter-increment: step-counter;
            content: counter(step-counter);
            position: absolute;
            left: 0;
            top: 0;
            background-color: #4A90E2;
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 24px;
        }
        
        footer {
            background-color: #333;
            color: white;
            padding: 40px 0;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .feature, .step {
                min-width: 100%;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>Reminder Service</h1>
            <p class="tagline">Helping businesses keep customers informed about their orders</p>
            <a href="/api/status" class="cta-button">Check API Status</a>
        </div>
    </header>
    
    <div class="container">
        <section>
            <h2>What We Do</h2>
            <p>Our reminder service helps businesses notify their customers about upcoming order expirations, ensuring clear communication and encouraging timely renewals.</p>
            
            <div class="features">
                <div class="feature">
                    <h3>Smart Reminders</h3>
                    <p>We send notifications at key intervals before expiration - one week, three days, and one day before the deadline - so customers have plenty of time to act.</p>
                </div>
                
                <div class="feature">
                    <h3>Multiple Languages</h3>
                    <p>Reach all your customers in their preferred language. We support English, Spanish, French, and German to ensure clear communication.</p>
                </div>
                
                <div class="feature">
                    <h3>Easy Setup</h3>
                    <p>Our API makes it simple to integrate with your existing systems. Just connect your order database and we handle the rest.</p>
                </div>
            </div>
        </section>
    </div>
    
    <div class="how-it-works">
        <div class="container">
            <h2>How It Works</h2>
            
            <div class="steps">
                <div class="step">
                    <h3>Connect Your Orders</h3>
                    <p>Use our API to send order information including customer details, order types, and expiration dates.</p>
                </div>
                
                <div class="step">
                    <h3>Configure Reminders</h3>
                    <p>Set up which reminders you want to send and when. You can customize the timing for different order types.</p>
                </div>
                
                <div class="step">
                    <h3>Personalize Templates</h3>
                    <p>Create your email templates with your company branding and personalized messages in multiple languages.</p>
                </div>
                
                <div class="step">
                    <h3>We Handle The Rest</h3>
                    <p>Our system automatically sends the right message at the right time, so you can focus on your business.</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container">
        <section>
            <h2>Ready to Get Started?</h2>
            <p>Check out our <a href="https://github.com/your-repo/reminder-service">documentation</a> to learn how to implement the Reminder Service API in your application.</p>
        </section>
    </div>
    
    <footer>
        <div class="container">
            <p>&copy; {{ date('Y') }} Reminder Service. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
