<?php
// app/Controllers/ContentController.php

namespace Controllers;

use Middleware\AuthMiddleware;

/**
 * Controlador de Gestión de Contenido
 */
class ContentController extends Controller {
    
    public function __construct() {
        parent::__construct();
        AuthMiddleware::check();
    }
    
    /**
     * Página principal de gestión de contenido
     */
    public function index() {
        AuthMiddleware::checkPermission('manage_content');
        
        $this->view('dashboard.content.index', [
            'title' => 'Control de Contenido - ALARA Admin',
            'csrf_token' => generateCSRFToken(),
            'user' => currentUser()
        ]);
    }
    
    /**
     * API: Generar contenido con IA
     */
    public function apiGenerate() {
        AuthMiddleware::checkPermission('manage_content');
        
        $data = $this->jsonInput();
        
        // Validar datos requeridos
        if (empty($data['pageTopic']) || empty($data['tone']) || empty($data['keywords'])) {
            $this->json([
                'success' => false,
                'message' => 'Todos los campos son requeridos'
            ], 400);
        }
        
        // Verificar si hay API key configurada
        if (!defined('OPENAI_API_KEY') && !defined('CLAUDE_API_KEY')) {
            $this->json([
                'success' => false,
                'message' => 'No hay API de IA configurada'
            ], 500);
        }
        
        try {
            $generatedContent = $this->generateWithAI($data);
            
            // Registrar actividad
            $this->logActivity('content_generated', 'content', null, 
                "Contenido generado para: {$data['pageTopic']}");
            
            $this->json([
                'success' => true,
                'data' => $generatedContent
            ]);
            
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Error al generar contenido: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generar contenido usando API de IA
     */
    private function generateWithAI($params) {
        // Construir el prompt
        $prompt = $this->buildPrompt($params);
        
        // Usar OpenAI si está configurado
        if (defined('OPENAI_API_KEY')) {
            return $this->generateWithOpenAI($prompt);
        }
        
        // Usar Claude si está configurado
        if (defined('CLAUDE_API_KEY')) {
            return $this->generateWithClaude($prompt);
        }
        
        // Fallback: generar contenido de ejemplo
        return $this->generateMockContent($params);
    }
    
    /**
     * Construir prompt para la IA
     */
    private function buildPrompt($params) {
        return "Genera contenido para una página web de venta de autos de lujo con las siguientes características:
        
Tema de la página: {$params['pageTopic']}
Tono: {$params['tone']}
Palabras clave: {$params['keywords']}

El contenido debe incluir:
1. Un título atractivo y optimizado para SEO
2. Un párrafo de introducción convincente
3. 3-4 secciones con subtítulos
4. Una llamada a la acción al final

El contenido debe ser profesional, persuasivo y orientado a la conversión de clientes interesados en autos de lujo seminuevos.";
    }
    
    /**
     * Generar con OpenAI
     */
    private function generateWithOpenAI($prompt) {
        $apiKey = OPENAI_API_KEY;
        $url = 'https://api.openai.com/v1/chat/completions';
        
        $data = [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'Eres un experto en marketing y ventas de autos de lujo.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'max_tokens' => 1000,
            'temperature' => 0.7
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new \Exception('Error en la API de OpenAI');
        }
        
        $result = json_decode($response, true);
        $content = $result['choices'][0]['message']['content'] ?? '';
        
        return $this->parseAIResponse($content);
    }
    
    /**
     * Generar con Claude (Anthropic)
     */
    private function generateWithClaude($prompt) {
        $apiKey = CLAUDE_API_KEY;
        $url = 'https://api.anthropic.com/v1/messages';
        
        $data = [
            'model' => 'claude-3-sonnet-20240229',
            'max_tokens' => 1000,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'x-api-key: ' . $apiKey,
            'anthropic-version: 2023-06-01',
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new \Exception('Error en la API de Claude');
        }
        
        $result = json_decode($response, true);
        $content = $result['content'][0]['text'] ?? '';
        
        return $this->parseAIResponse($content);
    }
    
    /**
     * Parsear respuesta de IA
     */
    private function parseAIResponse($content) {
        // Intentar extraer título y contenido
        $lines = explode("\n", $content);
        $title = '';
        $body = '';
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // El primer encabezado es el título
            if (empty($title) && (strpos($line, '#') === 0 || strlen($line) < 100)) {
                $title = str_replace('#', '', $line);
                $title = trim($title);
            } else {
                $body .= $line . "\n\n";
            }
        }
        
        return [
            'title' => $title ?: 'Contenido Generado',
            'content' => trim($body)
        ];
    }
    
    /**
     * Generar contenido de ejemplo (sin IA)
     */
    private function generateMockContent($params) {
        $templates = [
            'title' => [
                'Descubre los Mejores {topic} en ALARA',
                '{topic}: Lujo y Elegancia a tu Alcance',
                'Tu Próximo {topic} te Espera en ALARA'
            ],
            'intro' => [
                'En ALARA, entendemos que la búsqueda de {topic} es más que una simple compra; es una inversión en estilo de vida y calidad.',
                'Bienvenido a una experiencia única donde {topic} se combina con un servicio excepcional y opciones de financiamiento flexibles.',
                'Descubre nuestra exclusiva selección de {topic}, donde cada vehículo ha sido cuidadosamente seleccionado para superar tus expectativas.'
            ],
            'sections' => [
                [
                    'title' => 'Calidad Garantizada',
                    'content' => 'Cada vehículo en nuestro inventario pasa por una rigurosa inspección de {keywords}. Nos aseguramos de que cada auto cumpla con los más altos estándares de calidad.'
                ],
                [
                    'title' => 'Financiamiento a tu Medida',
                    'content' => 'Ofrecemos opciones de financiamiento flexibles para {topic}. Nuestro equipo de expertos te ayudará a encontrar el plan perfecto que se ajuste a tu presupuesto.'
                ],
                [
                    'title' => 'Experiencia Personalizada',
                    'content' => 'En ALARA, cada cliente es único. Por eso ofrecemos un servicio personalizado que hace que encontrar tu {topic} ideal sea una experiencia memorable.'
                ]
            ],
            'cta' => 'No esperes más para hacer realidad tu sueño. Contáctanos hoy mismo y descubre cómo podemos ayudarte a encontrar el {topic} perfecto para ti.'
        ];
        
        // Reemplazar placeholders
        $topic = $params['pageTopic'];
        $keywords = $params['keywords'];
        
        $title = str_replace('{topic}', $topic, $templates['title'][array_rand($templates['title'])]);
        $intro = str_replace('{topic}', $topic, $templates['intro'][array_rand($templates['intro'])]);
        
        $content = "<p>$intro</p>\n\n";
        
        foreach ($templates['sections'] as $section) {
            $sectionTitle = str_replace('{topic}', $topic, $section['title']);
            $sectionContent = str_replace(['{topic}', '{keywords}'], [$topic, $keywords], $section['content']);
            $content .= "<h2>$sectionTitle</h2>\n<p>$sectionContent</p>\n\n";
        }
        
        $cta = str_replace('{topic}', $topic, $templates['cta']);
        $content .= "<p><strong>$cta</strong></p>";
        
        return [
            'title' => $title,
            'content' => $content
        ];
    }
    
    /**
     * Registrar actividad
     */
    private function logActivity($action, $entityType, $entityId, $description) {
        $sql = "INSERT INTO activity_logs (user_id, action, entity_type, entity_id, description, ip_address) 
                VALUES (:user_id, :action, :entity_type, :entity_id, :description, :ip)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $_SESSION['user_id'] ?? null,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
        ]);
    }
}