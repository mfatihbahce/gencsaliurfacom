<?php
class SeoHelper {
    private $pdo;
    private $page_identifier;
    private $seo_data;
    
    public function __construct($pdo, $page_identifier) {
        $this->pdo = $pdo;
        $this->page_identifier = $page_identifier;
        $this->loadSeoData();
    }
    
    private function loadSeoData() {
        $stmt = $this->pdo->prepare("SELECT * FROM seo_settings WHERE page_identifier = ?");
        $stmt->execute([$this->page_identifier]);
        $this->seo_data = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getMetaTags() {
        if (!$this->seo_data) return '';
        
        $tags = [];
        $tags[] = "<title>" . htmlspecialchars($this->seo_data['title']) . "</title>";
        $tags[] = '<meta name="description" content="' . htmlspecialchars($this->seo_data['description']) . '">';
        
        if (!empty($this->seo_data['keywords'])) {
            $tags[] = '<meta name="keywords" content="' . htmlspecialchars($this->seo_data['keywords']) . '">';
        }
        
        // Open Graph tags
        if (!empty($this->seo_data['og_title'])) {
            $tags[] = '<meta property="og:title" content="' . htmlspecialchars($this->seo_data['og_title']) . '">';
        }
        
        if (!empty($this->seo_data['og_description'])) {
            $tags[] = '<meta property="og:description" content="' . htmlspecialchars($this->seo_data['og_description']) . '">';
        }
        
        if (!empty($this->seo_data['og_image'])) {
            $tags[] = '<meta property="og:image" content="' . htmlspecialchars($this->seo_data['og_image']) . '">';
        }
        
        // Canonical URL
        if (!empty($this->seo_data['canonical_url'])) {
            $tags[] = '<link rel="canonical" href="' . htmlspecialchars($this->seo_data['canonical_url']) . '">';
        }
        
        return implode("\n    ", $tags);
    }
} 