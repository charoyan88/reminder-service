<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;
    
    // Template types
    public const string TYPE_PRE_EXPIRATION = 'pre_expiration';
    public const string TYPE_POST_EXPIRATION = 'post_expiration';
    
    protected $fillable = [
        'name',
        'type',
        'subject',
        'body',
        'language_code',
        'is_active',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    /**
     * Parse the template with the given data
     * 
     * @param array $data The data to replace in the template
     * @return array The parsed subject and body
     */
    public function parse(array $data): array
    {
        $subject = $this->subject;
        $body = $this->body;
        
        foreach ($data as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $subject = str_replace($placeholder, $value, $subject);
            $body = str_replace($placeholder, $value, $body);
        }
        
        return [
            'subject' => $subject,
            'body' => $body,
        ];
    }
} 