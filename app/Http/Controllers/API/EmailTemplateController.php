<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmailTemplateController extends Controller
{
    /**
     * Get all email templates with optional filtering
     */
    public function index(Request $request): JsonResponse
    {
        $query = EmailTemplate::query();
        
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->has('language_code')) {
            $query->where('language_code', $request->language_code);
        }
        
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }
        
        $templates = $query->get();
        
        return response()->json(['templates' => $templates]);
    }
    
    /**
     * Store a new email template
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:pre_expiration,post_expiration',
            'subject' => 'required|string',
            'body' => 'required|string',
            'language_code' => 'required|string|size:2',
            'is_active' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $template = EmailTemplate::create($request->all());
        
        return response()->json(['template' => $template], 201);
    }
    
    /**
     * Update an existing email template
     */
    public function update(Request $request, $id): JsonResponse
    {
        $template = EmailTemplate::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'type' => 'in:pre_expiration,post_expiration',
            'subject' => 'string',
            'body' => 'string',
            'language_code' => 'string|size:2',
            'is_active' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $template->update($request->all());
        
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