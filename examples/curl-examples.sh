#!/bin/bash
# 
# Bash script with cURL examples for testing the n8n WordPress Integration API
# 
# Usage: Update the variables below and run: bash examples/curl-examples.sh
#

# Configuration
WORDPRESS_URL="https://your-wordpress-site.com"
API_KEY="your-secure-api-key-here"
API_BASE="${WORDPRESS_URL}/wp-json/n8n/v1"

echo "=== n8n WordPress Integration API - cURL Examples ==="
echo ""

# Example 1: Insert Data
echo "1. INSERT DATA"
echo "=============="
curl -X POST "${API_BASE}/insert" \
  -H "Content-Type: application/json" \
  -H "X-N8N-API-Key: ${API_KEY}" \
  -d '{
    "workflow_id": "test-workflow-001",
    "data": {
      "name": "John Doe",
      "email": "john@example.com",
      "message": "Hello from cURL test!"
    },
    "metadata": {
      "source": "curl-test",
      "timestamp": "'$(date -u +%Y-%m-%dT%H:%M:%SZ)'"
    }
  }'
echo -e "\n\n"

# Example 2: Get All Data
echo "2. GET ALL DATA (with pagination)"
echo "=================================="
curl -X GET "${API_BASE}/data?limit=10&offset=0" \
  -H "X-N8N-API-Key: ${API_KEY}"
echo -e "\n\n"

# Example 3: Get Data Filtered by Workflow ID
echo "3. GET DATA BY WORKFLOW ID"
echo "=========================="
curl -X GET "${API_BASE}/data?workflow_id=test-workflow-001&limit=5" \
  -H "X-N8N-API-Key: ${API_KEY}"
echo -e "\n\n"

# Example 4: Get Single Record
# Replace {ID} with an actual ID from your database
echo "4. GET SINGLE RECORD (replace ID)"
echo "=================================="
echo "curl -X GET \"${API_BASE}/data/1\" \\"
echo "  -H \"X-N8N-API-Key: ${API_KEY}\""
echo -e "\n"

# Example 5: Update Data
# Replace {ID} with an actual ID from your database
echo "5. UPDATE DATA (replace ID)"
echo "==========================="
echo "curl -X PUT \"${API_BASE}/update/1\" \\"
echo "  -H \"Content-Type: application/json\" \\"
echo "  -H \"X-N8N-API-Key: ${API_KEY}\" \\"
echo "  -d '{"
echo "    \"data\": {"
echo "      \"name\": \"Jane Doe\","
echo "      \"email\": \"jane@example.com\""
echo "    }"
echo "  }'"
echo -e "\n"

# Example 6: Delete Data
# Replace {ID} with an actual ID from your database
echo "6. DELETE DATA (replace ID)"
echo "==========================="
echo "curl -X DELETE \"${API_BASE}/delete/1\" \\"
echo "  -H \"X-N8N-API-Key: ${API_KEY}\""
echo -e "\n"

echo "=== Examples Complete ==="
