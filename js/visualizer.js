// N8N Workflow Visualizer using vis.js
class N8nWorkflowVisualizer {
    constructor(containerId, workflowData) {
        this.containerId = containerId;
        this.workflowData = typeof workflowData === 'string' 
            ? JSON.parse(workflowData) 
            : workflowData;
        this.network = null;
        this.nodes = null;
        this.edges = null;
    }

    // Parse n8n workflow JSON to vis.js format
    parseWorkflow() {
        const workflow = this.workflowData;

        // Extract nodes
        const nodes = workflow.nodes.map(node => {
            const nodeType = this.getNodeType(node.type);
            return {
                id: node.id,
                label: node.name,
                title: `${node.name}\n${nodeType.description}`,
                color: nodeType.color,
                shape: 'box',
                font: {
                    color: '#fff',
                    size: 14,
                    face: 'Arial, sans-serif',
                    bold: true
                },
                borderWidth: 3,
                borderWidthSelected: 4,
                margin: 15,
                widthConstraint: {
                    minimum: 150,
                    maximum: 250
                },
                heightConstraint: {
                    minimum: 50
                }
            };
        });

        // Extract connections/edges
        const edges = [];
        if (workflow.connections) {
            Object.keys(workflow.connections).forEach(sourceNodeName => {
                const sourceNode = workflow.nodes.find(n => n.name === sourceNodeName);
                if (sourceNode) {
                    const connections = workflow.connections[sourceNodeName];
                    Object.keys(connections).forEach(outputType => {
                        connections[outputType].forEach(connectionGroup => {
                            connectionGroup.forEach(connection => {
                                const targetNode = workflow.nodes.find(n => n.name === connection.node);
                                if (targetNode) {
                                    edges.push({
                                        from: sourceNode.id,
                                        to: targetNode.id,
                                        arrows: 'to',
                                        color: {
                                            color: '#848484',
                                            highlight: '#2B7CE9'
                                        },
                                        width: 2,
                                        smooth: {
                                            type: 'cubicBezier',
                                            forceDirection: 'horizontal',
                                            roundness: 0.4
                                        }
                                    });
                                }
                            });
                        });
                    });
                }
            });
        }

        return { nodes, edges };
    }

    // Get node type information (colors and descriptions)
    getNodeType(nodeType) {
        const nodeTypes = {
            'n8n-nodes-base.formTrigger': {
                color: { background: '#FF6B6B', border: '#FF5252' },
                description: 'Form Trigger - Starts workflow on form submission'
            },
            '@n8n/n8n-nodes-langchain.lmChatGoogleGemini': {
                color: { background: '#4285F4', border: '#1976D2' },
                description: 'Google Gemini - AI Chat Model'
            },
            '@n8n/n8n-nodes-langchain.outputParserStructured': {
                color: { background: '#9C27B0', border: '#7B1FA2' },
                description: 'Output Parser - Structures AI responses'
            },
            'n8n-nodes-base.googleCalendarTool': {
                color: { background: '#34A853', border: '#2E7D32' },
                description: 'Google Calendar - Calendar operations'
            },
            '@n8n/n8n-nodes-langchain.memoryBufferWindow': {
                color: { background: '#FF9800', border: '#F57C00' },
                description: 'Memory Buffer - Conversation memory'
            },
            'n8n-nodes-base.gmailTool': {
                color: { background: '#EA4335', border: '#D32F2F' },
                description: 'Gmail - Email operations'
            },
            'n8n-nodes-base.telegram': {
                color: { background: '#0088CC', border: '#0277BD' },
                description: 'Telegram - Messaging'
            },
            // Default for unknown node types
            'default': {
                color: { background: '#E0E0E0', border: '#BDBDBD' },
                description: 'Node'
            }
        };

        return nodeTypes[nodeType] || nodeTypes['default'];
    }

    // Create and render the visualization
    render() {
        const container = document.getElementById(this.containerId);
        if (!container) {
            console.error(`Container with id '${this.containerId}' not found`);
            return;
        }

        const { nodes, edges } = this.parseWorkflow();

        // Create DataSets
        this.nodes = new vis.DataSet(nodes);
        this.edges = new vis.DataSet(edges);

        // Configure the network
        const data = {
            nodes: this.nodes,
            edges: this.edges
        };

        const options = {
            layout: {
                hierarchical: {
                    enabled: true,
                    direction: 'LR', // Left to Right
                    sortMethod: 'directed',
                    nodeSpacing: 200,
                    levelSeparation: 300,
                    treeSpacing: 200
                }
            },
            physics: {
                enabled: false // Disable physics for cleaner layout
            },
            interaction: {
                dragNodes: true,
                dragView: true,
                zoomView: true,
                selectConnectedEdges: true
            },
            edges: {
                smooth: {
                    type: 'cubicBezier',
                    forceDirection: 'horizontal',
                    roundness: 0.4
                },
                width: 3,
                color: {
                    color: '#848484',
                    highlight: '#2B7CE9'
                }
            },
            nodes: {
                chosen: {
                    node: function(values, id, selected, hovering) {
                        values.shadow = true;
                        values.shadowSize = 15;
                        values.shadowX = 5;
                        values.shadowY = 5;
                        values.shadowColor = 'rgba(0,0,0,0.3)';
                    }
                },
                font: {
                    size: 16,
                    color: '#333333'
                },
                borderWidth: 3,
                borderWidthSelected: 4,
                size: 25
            }
        };

        // Create network
        this.network = new vis.Network(container, data, options);

        // Fit network to view after a short delay
        setTimeout(() => {
            if (this.network) {
                this.network.fit({
                    animation: {
                        duration: 1000,
                        easingFunction: 'easeInOutQuad'
                    }
                });
            }
        }, 500);

        // Add event listeners
        this.network.on('selectNode', (params) => {
            if (params.nodes.length > 0) {
                const nodeId = params.nodes[0];
                const node = this.workflowData.nodes ? this.workflowData.nodes.find(n => n.id === nodeId) : null;
                if (node) {
                    this.showNodeDetails(node);
                }
            }
        });
    }

    // Show node details in a modal or tooltip
    showNodeDetails(node) {
        const details = `
            <strong>Node:</strong> ${node.name}<br>
            <strong>Type:</strong> ${node.type}<br>
            <strong>ID:</strong> ${node.id}<br>
            ${node.parameters ? `<strong>Parameters:</strong> ${JSON.stringify(node.parameters, null, 2)}` : ''}
        `;
        
        // You can implement a modal here or just log for now
        console.log('Node Details:', node);
        
        // Simple tooltip implementation
        const tooltip = document.createElement('div');
        tooltip.innerHTML = details;
        tooltip.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 10px;
            max-width: 300px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            z-index: 1000;
            font-size: 12px;
        `;
        
        document.body.appendChild(tooltip);
        
        setTimeout(() => {
            document.body.removeChild(tooltip);
        }, 5000);
    }

    // Update workflow data
    updateWorkflow(newWorkflowData) {
        this.workflowData = newWorkflowData;
        this.render();
    }

    // Destroy the visualization
    destroy() {
        if (this.network) {
            this.network.destroy();
        }
    }
}

// Custom Web Component for N8N Workflow Visualization
class N8nWorkflowViewer extends HTMLElement {
    constructor() {
        super();
        this.visualizer = null;
        this.containerId = null;
    }

    connectedCallback() {
        const workflow = this.getAttribute('workflow');
        const width = this.getAttribute('width') || '100%';
        const height = this.getAttribute('height') || '400px';
        
        // Generate unique container ID
        this.containerId = 'workflow-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
        
        // Create container
        this.innerHTML = `
            <div class="n8n-workflow-container">
                <div class="workflow-header">
                    <h3>${this.getWorkflowName(workflow)}</h3>
                    <div class="workflow-controls">
                        <button onclick="this.parentElement.parentElement.parentElement.fitToScreen()" class="fit-btn">
                            📐 Fit to Screen
                        </button>
                        <button onclick="this.parentElement.parentElement.parentElement.exportImage()" class="export-btn">
                            💾 Export Image
                        </button>
                    </div>
                </div>
                <div id="${this.containerId}" style="width: ${width}; height: ${height}; border: 1px solid #ddd; border-radius: 8px; background: #fff;"></div>
            </div>
        `;

        // Apply styles
        const style = document.createElement('style');
        style.textContent = `
            .n8n-workflow-container {
                font-family: Arial, sans-serif;
                margin: 10px 0;
            }
            .workflow-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 10px;
                padding: 10px 0;
            }
            .workflow-header h3 {
                margin: 0;
                color: #333;
                font-size: 18px;
            }
            .workflow-controls {
                display: flex;
                gap: 10px;
            }
            .fit-btn, .export-btn {
                background: #007cba;
                color: white;
                border: none;
                padding: 8px 12px;
                border-radius: 4px;
                cursor: pointer;
                font-size: 12px;
            }
            .fit-btn:hover, .export-btn:hover {
                background: #005a8b;
            }
        `;
        this.appendChild(style);

        // Initialize visualizer with a small delay to ensure DOM is ready
        setTimeout(() => {
            this.initializeVisualizer(workflow);
        }, 100);
    }

    getWorkflowName(workflowJson) {
        try {
            const workflow = JSON.parse(workflowJson);
            return workflow.name || 'Unnamed Workflow';
        } catch (e) {
            return 'Workflow Visualization';
        }
    }

    initializeVisualizer(workflow) {
        // Double check that vis is loaded and container exists
        if (typeof vis !== 'undefined' && document.getElementById(this.containerId)) {
            this.visualizer = new N8nWorkflowVisualizer(this.containerId, workflow);
            this.visualizer.render();
        } else {
            // Retry after a short delay
            setTimeout(() => this.initializeVisualizer(workflow), 200);
        }
    }

    fitToScreen() {
        if (this.visualizer && this.visualizer.network) {
            this.visualizer.network.fit();
        }
    }

    exportImage() {
        if (this.visualizer && this.visualizer.network) {
            const canvas = this.visualizer.network.canvas;
            const dataURL = canvas.getCanvas().toDataURL();
            
            // Create download link
            const link = document.createElement('a');
            link.download = 'workflow.png';
            link.href = dataURL;
            link.click();
        }
    }

    disconnectedCallback() {
        if (this.visualizer) {
            this.visualizer.destroy();
        }
    }
}

// Register the custom element
customElements.define('n8n-workflow-viewer', N8nWorkflowViewer);