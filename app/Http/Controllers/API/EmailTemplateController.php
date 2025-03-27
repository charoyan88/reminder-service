<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmailTemplate\IndexRequest;
use App\Http\Requests\EmailTemplate\StoreRequest;
use App\Http\Requests\EmailTemplate\UpdateRequest;
use App\Models\EmailTemplate;
use Illuminate\Http\JsonResponse;

class EmailTemplateController extends Controller
{
    /**
     * Get all email templates with optional filtering
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $query = EmailTemplate::query();
        
        if (isset($validatedData['type'])) {
            $query->where('type', $validatedData['type']);
        }
        
        if (isset($validatedData['language_code'])) {
            $query->where('language_code', $validatedData['language_code']);
        }
        
        if (isset($validatedData['is_active'])) {
            $query->where('is_active', $validatedData['is_active']);
        }
        
        $templates = $query->get();
        
        return response()->json(['templates' => $templates]);
    }
    
    /**
     * Store a new email template
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $template = EmailTemplate::create($validatedData);
        
        return response()->json(['template' => $template], 201);
    }
    
    /**
     * Update an existing email template
     */
    public function update(UpdateRequest $request, $id): JsonResponse
    {
        $template = EmailTemplate::findOrFail($id);
        $validatedData = $request->validated();
        $template->update($validatedData);
        
        return response()->json(['template' => $template]);
    }
    
    /**
     * Delete an email template
     */
    public function destroy($id): JsonResponse
    {
        $template = EmailTemplate::findOrFail($id);
        $template->delete();
        
        return response()->json(null, 204);
    }
    
    /**
     * Preview a template with sample data
     */
    public function preview($id): JsonResponse
    {
        $template = EmailTemplate::findOrFail($id);
        
        // Sample data for preview
        $sampleData = [
            'business_name' => 'Sample Business',
            'order_type' => 'Premium Subscription',
            'expiration_date' => now()->addMonths(3)->format('F j, Y'),
            'interval' => $template->type === 'pre_expiration' 
                ? '7 days before expiration' 
                : '3 days after expiration',
        ];
        
        $parsedTemplate = $template->parse($sampleData);
        
        return response()->json([
            'template' => $template,
            'preview' => [
                'subject' => $parsedTemplate['subject'],
                'body' => $parsedTemplate['body'],
            ],
        ]);
    }
} 