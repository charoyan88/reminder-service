<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // English Templates
        $this->createTemplates('en');
        
        // Spanish Templates
        $this->createTemplates('es');
        
        // French Templates
        $this->createTemplates('fr');
        
        // German Templates
        $this->createTemplates('de');
    }
    
    /**
     * Create templates for a specific language
     */
    private function createTemplates(string $langCode): void
    {
        // Pre-expiration template
        EmailTemplate::create([
            'name' => "Pre-expiration Template ($langCode)",
            'type' => 'pre_expiration',
            'subject' => "{{order_type}} Expiration Notice",
            'body' => $this->getPreExpirationBody($langCode),
            'language_code' => $langCode,
            'is_active' => true,
        ]);
        
        // Post-expiration template
        EmailTemplate::create([
            'name' => "Post-expiration Template ($langCode)",
            'type' => 'post_expiration',
            'subject' => "{{order_type}} Has Expired",
            'body' => $this->getPostExpirationBody($langCode),
            'language_code' => $langCode,
            'is_active' => true,
        ]);
    }
    
    /**
     * Get pre-expiration template body
     */
    private function getPreExpirationBody($language)
    {
        $translations = [
            'en' => [
                'title' => 'Your order will expire soon',
                'greeting' => 'Hello there,',
                'intro' => 'Just a friendly reminder that your order is about to expire.',
                'details' => 'Here are the details:',
                'business' => 'Business:',
                'orderType' => 'Order Type:',
                'expiryDate' => 'Expiration Date:',
                'action' => 'To make sure you don\'t experience any interruptions, we recommend renewing your order before it expires.',
                'button' => 'Renew Now',
                'closing' => 'If you have any questions, just reply to this email. We\'re here to help!',
                'thanks' => 'Thanks for your business,',
                'team' => 'The Customer Care Team'
            ],
            'es' => [
                'title' => 'Su pedido expirará pronto',
                'greeting' => '¡Hola!',
                'intro' => 'Un recordatorio amistoso de que su pedido está a punto de expirar.',
                'details' => 'Estos son los detalles:',
                'business' => 'Negocio:',
                'orderType' => 'Tipo de Orden:',
                'expiryDate' => 'Fecha de Expiración:',
                'action' => 'Para asegurarse de no experimentar interrupciones, le recomendamos renovar su pedido antes de que expire.',
                'button' => 'Renovar Ahora',
                'closing' => 'Si tiene alguna pregunta, simplemente responda a este correo electrónico. ¡Estamos aquí para ayudar!',
                'thanks' => 'Gracias por su preferencia,',
                'team' => 'El Equipo de Atención al Cliente'
            ],
            'fr' => [
                'title' => 'Votre commande expirera bientôt',
                'greeting' => 'Bonjour,',
                'intro' => 'Un petit rappel amical que votre commande est sur le point d\'expirer.',
                'details' => 'Voici les détails:',
                'business' => 'Entreprise:',
                'orderType' => 'Type de Commande:',
                'expiryDate' => 'Date d\'Expiration:',
                'action' => 'Pour vous assurer de ne pas subir d\'interruptions, nous vous recommandons de renouveler votre commande avant qu\'elle n\'expire.',
                'button' => 'Renouveler Maintenant',
                'closing' => 'Si vous avez des questions, répondez simplement à cet e-mail. Nous sommes là pour vous aider!',
                'thanks' => 'Merci pour votre confiance,',
                'team' => 'L\'Équipe du Service Client'
            ],
            'de' => [
                'title' => 'Ihre Bestellung läuft bald ab',
                'greeting' => 'Hallo,',
                'intro' => 'Eine freundliche Erinnerung, dass Ihre Bestellung bald abläuft.',
                'details' => 'Hier sind die Details:',
                'business' => 'Unternehmen:',
                'orderType' => 'Bestelltyp:',
                'expiryDate' => 'Ablaufdatum:',
                'action' => 'Um sicherzustellen, dass Sie keine Unterbrechungen erleben, empfehlen wir Ihnen, Ihre Bestellung zu erneuern, bevor sie abläuft.',
                'button' => 'Jetzt Erneuern',
                'closing' => 'Falls Sie Fragen haben, antworten Sie einfach auf diese E-Mail. Wir sind hier, um zu helfen!',
                'thanks' => 'Vielen Dank für Ihr Vertrauen,',
                'team' => 'Das Kundenbetreuungsteam'
            ]
        ];

        $t = $translations[$language];

        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #4A90E2; color: white; padding: 15px; text-align: center; border-radius: 5px 5px 0 0; }
                .content { background-color: #fff; padding: 20px; border: 1px solid #ddd; border-top: none; border-radius: 0 0 5px 5px; }
                .info { background-color: #f9f9f9; padding: 15px; margin: 15px 0; border-radius: 5px; }
                .button { display: inline-block; background-color: #4A90E2; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { font-size: 14px; color: #888; margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>{$t['title']}</h1>
                </div>
                <div class="content">
                    <p>{$t['greeting']}</p>
                    <p>{$t['intro']}</p>
                    
                    <p>{$t['details']}</p>
                    <div class="info">
                        <p><strong>{$t['business']}</strong> {{business_name}}</p>
                        <p><strong>{$t['orderType']}</strong> {{order_type}}</p>
                        <p><strong>{$t['expiryDate']}</strong> {{expiration_date}}</p>
                    </div>
                    
                    <p>{$t['action']}</p>
                    <center><a href="{{renewal_link}}" class="button">{$t['button']}</a></center>
                    
                    <p>{$t['closing']}</p>
                    
                    <div class="footer">
                        <p>{$t['thanks']}<br>{$t['team']}</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }
    
    /**
     * Get post-expiration template body
     */
    private function getPostExpirationBody($language)
    {
        $translations = [
            'en' => [
                'title' => 'Your order has expired',
                'greeting' => 'Hello there,',
                'intro' => 'We noticed that your order has recently expired.',
                'details' => 'Here are the details:',
                'business' => 'Business:',
                'orderType' => 'Order Type:',
                'expiryDate' => 'Expiration Date:',
                'action' => 'Don\'t worry, it\'s not too late! You can still renew your order to continue enjoying our services without any gaps.',
                'button' => 'Renew Now',
                'closing' => 'Need help or have questions? We\'re just an email away. Simply reply to this message and we\'ll get back to you quickly.',
                'thanks' => 'Thanks for being our customer,',
                'team' => 'The Customer Care Team'
            ],
            'es' => [
                'title' => 'Su pedido ha expirado',
                'greeting' => '¡Hola!',
                'intro' => 'Hemos notado que su pedido ha expirado recientemente.',
                'details' => 'Estos son los detalles:',
                'business' => 'Negocio:',
                'orderType' => 'Tipo de Orden:',
                'expiryDate' => 'Fecha de Expiración:',
                'action' => '¡No se preocupe, no es demasiado tarde! Todavía puede renovar su pedido para seguir disfrutando de nuestros servicios sin interrupciones.',
                'button' => 'Renovar Ahora',
                'closing' => '¿Necesita ayuda o tiene preguntas? Estamos a solo un correo electrónico de distancia. Simplemente responda a este mensaje y nos pondremos en contacto con usted rápidamente.',
                'thanks' => 'Gracias por ser nuestro cliente,',
                'team' => 'El Equipo de Atención al Cliente'
            ],
            'fr' => [
                'title' => 'Votre commande a expiré',
                'greeting' => 'Bonjour,',
                'intro' => 'Nous avons remarqué que votre commande a récemment expiré.',
                'details' => 'Voici les détails:',
                'business' => 'Entreprise:',
                'orderType' => 'Type de Commande:',
                'expiryDate' => 'Date d\'Expiration:',
                'action' => 'Ne vous inquiétez pas, il n\'est pas trop tard! Vous pouvez toujours renouveler votre commande pour continuer à profiter de nos services sans interruption.',
                'button' => 'Renouveler Maintenant',
                'closing' => 'Besoin d\'aide ou avez-vous des questions? Nous sommes à un email près. Répondez simplement à ce message et nous vous répondrons rapidement.',
                'thanks' => 'Merci d\'être notre client,',
                'team' => 'L\'Équipe du Service Client'
            ],
            'de' => [
                'title' => 'Ihre Bestellung ist abgelaufen',
                'greeting' => 'Hallo,',
                'intro' => 'Wir haben festgestellt, dass Ihre Bestellung kürzlich abgelaufen ist.',
                'details' => 'Hier sind die Details:',
                'business' => 'Unternehmen:',
                'orderType' => 'Bestelltyp:',
                'expiryDate' => 'Ablaufdatum:',
                'action' => 'Keine Sorge, es ist noch nicht zu spät! Sie können Ihre Bestellung immer noch erneuern, um unsere Dienste ohne Unterbrechung weiter zu nutzen.',
                'button' => 'Jetzt Erneuern',
                'closing' => 'Brauchen Sie Hilfe oder haben Sie Fragen? Wir sind nur eine E-Mail entfernt. Antworten Sie einfach auf diese Nachricht und wir melden uns schnell bei Ihnen.',
                'thanks' => 'Danke, dass Sie unser Kunde sind,',
                'team' => 'Das Kundenbetreuungsteam'
            ]
        ];

        $t = $translations[$language];

        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #FF6B6B; color: white; padding: 15px; text-align: center; border-radius: 5px 5px 0 0; }
                .content { background-color: #fff; padding: 20px; border: 1px solid #ddd; border-top: none; border-radius: 0 0 5px 5px; }
                .info { background-color: #f9f9f9; padding: 15px; margin: 15px 0; border-radius: 5px; }
                .button { display: inline-block; background-color: #FF6B6B; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { font-size: 14px; color: #888; margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>{$t['title']}</h1>
                </div>
                <div class="content">
                    <p>{$t['greeting']}</p>
                    <p>{$t['intro']}</p>
                    
                    <p>{$t['details']}</p>
                    <div class="info">
                        <p><strong>{$t['business']}</strong> {{business_name}}</p>
                        <p><strong>{$t['orderType']}</strong> {{order_type}}</p>
                        <p><strong>{$t['expiryDate']}</strong> {{expiration_date}}</p>
                    </div>
                    
                    <p>{$t['action']}</p>
                    <center><a href="{{renewal_link}}" class="button">{$t['button']}</a></center>
                    
                    <p>{$t['closing']}</p>
                    
                    <div class="footer">
                        <p>{$t['thanks']}<br>{$t['team']}</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }
} 