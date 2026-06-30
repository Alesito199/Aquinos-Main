// Demo workflow data for testing
const demoWorkflows = {
  ecommerce: {
    "id": "ecommerce-automation",
    "name": "🛒 E-commerce Order Processing",
    "nodes": [
      {
        "id": "webhook-trigger",
        "name": "New Order Webhook",
        "type": "n8n-nodes-base.webhook",
        "position": [0, 0]
      },
      {
        "id": "validate-order",
        "name": "Validate Order",
        "type": "n8n-nodes-base.function",
        "position": [200, 0]
      },
      {
        "id": "check-inventory", 
        "name": "Check Inventory",
        "type": "n8n-nodes-base.http",
        "position": [400, 0]
      },
      {
        "id": "send-confirmation",
        "name": "Send Confirmation Email",
        "type": "n8n-nodes-base.emailSend",
        "position": [600, -100]
      },
      {
        "id": "create-shipment",
        "name": "Create Shipment",
        "type": "n8n-nodes-base.http",
        "position": [600, 100]
      },
      {
        "id": "notify-warehouse",
        "name": "Notify Warehouse",
        "type": "n8n-nodes-base.slack",
        "position": [800, 100]
      },
      {
        "id": "update-crm",
        "name": "Update CRM",
        "type": "n8n-nodes-base.salesforce",
        "position": [800, -100]
      }
    ],
    "active": true,
    "connections": {
      "New Order Webhook": {
        "main": [[{"node": "Validate Order", "type": "main", "index": 0}]]
      },
      "Validate Order": {
        "main": [[{"node": "Check Inventory", "type": "main", "index": 0}]]
      },
      "Check Inventory": {
        "main": [
          [{"node": "Send Confirmation Email", "type": "main", "index": 0}],
          [{"node": "Create Shipment", "type": "main", "index": 0}]
        ]
      },
      "Send Confirmation Email": {
        "main": [[{"node": "Update CRM", "type": "main", "index": 0}]]
      },
      "Create Shipment": {
        "main": [[{"node": "Notify Warehouse", "type": "main", "index": 0}]]
      }
    }
  },
  
  dataProcessing: {
    "id": "data-pipeline",
    "name": "📊 Advanced Data Pipeline",
    "nodes": [
      {
        "id": "schedule-trigger",
        "name": "Daily Schedule",
        "type": "n8n-nodes-base.cron",
        "position": [0, 0]
      },
      {
        "id": "fetch-data-api1",
        "name": "Fetch Sales Data",
        "type": "n8n-nodes-base.http",
        "position": [200, -100]
      },
      {
        "id": "fetch-data-api2",
        "name": "Fetch Customer Data",
        "type": "n8n-nodes-base.http",
        "position": [200, 100]
      },
      {
        "id": "merge-data",
        "name": "Merge Datasets",
        "type": "n8n-nodes-base.merge",
        "position": [400, 0]
      },
      {
        "id": "transform-data",
        "name": "Transform & Clean",
        "type": "n8n-nodes-base.function",
        "position": [600, 0]
      },
      {
        "id": "ai-analysis",
        "name": "AI Analysis",
        "type": "@n8n/n8n-nodes-langchain.lmChatGoogleGemini",
        "position": [800, 0]
      },
      {
        "id": "save-to-db",
        "name": "Save to Database",
        "type": "n8n-nodes-base.postgres",
        "position": [1000, -100]
      },
      {
        "id": "generate-report",
        "name": "Generate Report",
        "type": "n8n-nodes-base.function",
        "position": [1000, 100]
      },
      {
        "id": "send-report",
        "name": "Email Report",
        "type": "n8n-nodes-base.emailSend",
        "position": [1200, 0]
      }
    ],
    "active": true,
    "connections": {
      "Daily Schedule": {
        "main": [
          [{"node": "Fetch Sales Data", "type": "main", "index": 0}],
          [{"node": "Fetch Customer Data", "type": "main", "index": 0}]
        ]
      },
      "Fetch Sales Data": {
        "main": [[{"node": "Merge Datasets", "type": "main", "index": 0}]]
      },
      "Fetch Customer Data": {
        "main": [[{"node": "Merge Datasets", "type": "main", "index": 1}]]
      },
      "Merge Datasets": {
        "main": [[{"node": "Transform & Clean", "type": "main", "index": 0}]]
      },
      "Transform & Clean": {
        "main": [[{"node": "AI Analysis", "type": "main", "index": 0}]]
      },
      "AI Analysis": {
        "main": [
          [{"node": "Save to Database", "type": "main", "index": 0}],
          [{"node": "Generate Report", "type": "main", "index": 0}]
        ]
      },
      "Save to Database": {
        "main": [[{"node": "Email Report", "type": "main", "index": 0}]]
      },
      "Generate Report": {
        "main": [[{"node": "Email Report", "type": "main", "index": 0}]]
      }
    }
  }
};

// Function to add demo workflows to the page
function addDemoWorkflows() {
  const workflowsGrid = document.querySelector('.workflows-grid');
  
  if (workflowsGrid) {
    // Add E-commerce workflow
    const ecommerceItem = document.createElement('div');
    ecommerceItem.className = 'workflow-item';
    ecommerceItem.innerHTML = `
      <div class="workflow-preview">
        <n8n-workflow-viewer 
          workflow='${JSON.stringify(demoWorkflows.ecommerce)}'
          width="100%"
          height="400px">
        </n8n-workflow-viewer>
      </div>
      <div class="workflow-info">
        <h3>Procesamiento de Pedidos</h3>
        <p>Automatización completa del flujo de pedidos desde recepción hasta entrega.</p>
        <div class="workflow-stats">
          <span class="stat">⚡ 7 nodos</span>
          <span class="stat">🛒 E-commerce</span>
        </div>
      </div>
    `;
    
    // Add Data Pipeline workflow
    const dataItem = document.createElement('div');
    dataItem.className = 'workflow-item';
    dataItem.innerHTML = `
      <div class="workflow-preview">
        <n8n-workflow-viewer 
          workflow='${JSON.stringify(demoWorkflows.dataProcessing)}'
          width="100%"
          height="400px">
        </n8n-workflow-viewer>
      </div>
      <div class="workflow-info">
        <h3>Pipeline de Datos Avanzado</h3>
        <p>Procesamiento automático de datos con análisis IA y reportes inteligentes.</p>
        <div class="workflow-stats">
          <span class="stat">⚡ 9 nodos</span>
          <span class="stat">📊 Big Data</span>
        </div>
      </div>
    `;
    
    // Append to grid
    workflowsGrid.appendChild(ecommerceItem);
    workflowsGrid.appendChild(dataItem);
  }
}

// Initialize demo workflows when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
  // Wait a bit for vis.js to load
  setTimeout(() => {
    addDemoWorkflows();
  }, 1000);
});