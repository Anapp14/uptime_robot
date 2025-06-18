<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Server Monitoring Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e1e2f 0%, #2c2c3e 100%);
            color: #e0e0e0;
            min-height: 100vh;
            padding: 20px;
        }
        
        .dashboard-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .dashboard-header h1 {
            color: #ffffff;
            font-size: 2.5rem;
            font-weight: 300;
            margin-bottom: 10px;
        }
        
        .dashboard-header .subtitle {
            color: #a0a0a0;
            font-size: 1.1rem;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .chart-container {
            background: rgba(44, 44, 62, 0.8);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .chart-container h3 {
            color: #ffffff;
            margin-bottom: 20px;
            font-size: 1.4rem;
            font-weight: 500;
        }
        
        .chart-wrapper {
            position: relative;
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .incidents-panel {
            background: rgba(44, 44, 62, 0.8);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            max-height: 400px;
            overflow-y: auto;
        }
        
        .incidents-panel h3 {
            color: #ff4757;
            margin-bottom: 20px;
            font-size: 1.4rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .incident-item {
            background: rgba(255, 71, 87, 0.1);
            border: 1px solid rgba(255, 71, 87, 0.3);
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }
        
        .incident-item:hover {
            background: rgba(255, 71, 87, 0.2);
            transform: translateX(5px);
        }
        
        .incident-name {
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 5px;
        }
        
        .incident-time {
            font-size: 0.85rem;
            color: #a0a0a0;
        }
        
        .servers-table-container {
            background: rgba(44, 44, 62, 0.8);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            overflow-x: auto;
        }
        
        .servers-table-container h3 {
            color: #ffffff;
            margin-bottom: 20px;
            font-size: 1.4rem;
            font-weight: 500;
        }
        
        .servers-table {
            width: 100%;
            border-collapse: collapse;
            background: transparent;
        }
        
        .servers-table th {
            background: rgba(58, 58, 77, 0.8);
            color: #ffffff;
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        }
        
        .servers-table td {
            padding: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: background-color 0.3s ease;
        }
        
        .servers-table tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-up {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }
        
        .status-down {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }
        
        .status-paused {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }
        
        .status-unknown {
            background: rgba(108, 117, 125, 0.2);
            color: #6c757d;
            border: 1px solid rgba(108, 117, 125, 0.3);
        }
        
        .uptime-bar {
            width: 100%;
            height: 8px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            overflow: hidden;
            margin-top: 5px;
        }
        
        .uptime-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #20c997);
            border-radius: 4px;
            transition: width 0.3s ease;
        }
        
        .countdown-box {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(44, 44, 62, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            z-index: 1000;
        }
        
        .countdown-box span {
            font-weight: bold;
            color: #00d4ff;
            font-size: 18px;
        }
        
        .stats-summary {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .stat-item {
            text-align: center;
            padding: 15px 25px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #a0a0a0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .no-incidents {
            text-align: center;
            color: #28a745;
            padding: 20px;
            font-style: italic;
        }
        
        @media (max-width: 1024px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .incidents-panel {
                max-height: 300px;
            }
        }
        
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .dashboard-header h1 {
                font-size: 2rem;
            }
            
            .stats-summary {
                gap: 15px;
            }
            
            .stat-item {
                padding: 10px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <h1>🖥️ Server Monitoring Dashboard</h1>
        <p class="subtitle">Real-time server status and performance monitoring</p>
    </div>
    
    <div class="countdown-box">
        Next Update: <span id="countdown">30</span>s
    </div>
    
    <div class="dashboard-grid">
        <div class="chart-container">
            <h3>📊 Overall Server Status</h3>
            <div class="chart-wrapper">
                <canvas id="statusChart"></canvas>
            </div>
            <div class="stats-summary" id="statsSummary">
                <!-- Stats will be populated by JavaScript -->
            </div>
        </div>
        
        <div class="incidents-panel">
            <h3>🚨 Active Incidents</h3>
            <div id="incidentsList">
                <!-- Incidents will be populated by JavaScript -->
            </div>
        </div>
    </div>
    
    <div class="servers-table-container">
        <h3>📋 All Servers Status</h3>
        <table class="servers-table">
            <thead>
                <tr>
                    <th>Server Group</th>
                    <th>Monitor Name</th>
                    <th>Status</th>
                    <th>24h Uptime</th>
                    <th>Uptime Progress</th>
                </tr>
            </thead>
            <tbody id="serversTableBody">
                <!-- Table rows will be populated by JavaScript -->
            </tbody>
        </table>
    </div>

    <script>
        let countdown = 30;
        let statusChart = null;
        
        // Initialize Chart.js
        function initChart() {
            const ctx = document.getElementById('statusChart').getContext('2d');
            statusChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Up', 'Down', 'Paused', 'Unknown'],
                    datasets: [{
                        data: [0, 0, 0, 0],
                        backgroundColor: [
                            '#28a745',
                            '#dc3545',
                            '#ffc107',
                            '#6c757d'
                        ],
                        borderColor: [
                            '#28a745',
                            '#dc3545',
                            '#ffc107',
                            '#6c757d'
                        ],
                        borderWidth: 2,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: '#e0e0e0',
                                padding: 20,
                                font: {
                                    size: 12
                                }
                            }
                        }
                    },
                    cutout: '60%'
                }
            });
        }
        
        function getStatusInfo(status) {
            switch(status) {
                case 2: return { text: 'UP', class: 'status-up' };
                case 8:
                case 9: return { text: 'DOWN', class: 'status-down' };
                case 0: return { text: 'PAUSED', class: 'status-paused' };
                default: return { text: 'UNKNOWN', class: 'status-unknown' };
            }
        }
        
        function updateChart(monitors) {
            const statusCounts = { up: 0, down: 0, paused: 0, unknown: 0 };
            
            monitors.forEach(monitor => {
                switch(monitor.status) {
                    case 2: statusCounts.up++; break;
                    case 8:
                    case 9: statusCounts.down++; break;
                    case 0: statusCounts.paused++; break;
                    default: statusCounts.unknown++; break;
                }
            });
            
            statusChart.data.datasets[0].data = [
                statusCounts.up,
                statusCounts.down,
                statusCounts.paused,
                statusCounts.unknown
            ];
            statusChart.update();
            
            // Update stats summary
            const total = monitors.length;
            const upPercentage = total > 0 ? ((statusCounts.up / total) * 100).toFixed(1) : 0;
            
            document.getElementById('statsSummary').innerHTML = `
                <div class="stat-item">
                    <div class="stat-number" style="color: #28a745;">${statusCounts.up}</div>
                    <div class="stat-label">Online</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" style="color: #dc3545;">${statusCounts.down}</div>
                    <div class="stat-label">Offline</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" style="color: #00d4ff;">${upPercentage}%</div>
                    <div class="stat-label">Availability</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" style="color: #ffffff;">${total}</div>
                    <div class="stat-label">Total</div>
                </div>
            `;
        }
        
        function updateIncidents(monitors) {
            const incidents = monitors.filter(monitor => monitor.status === 8 || monitor.status === 9);
            const incidentsList = document.getElementById('incidentsList');
            
            if (incidents.length === 0) {
                incidentsList.innerHTML = '<div class="no-incidents">✅ No active incidents</div>';
                return;
            }
            
            incidentsList.innerHTML = incidents.map(incident => `
                <div class="incident-item">
                    <div class="incident-name">${incident.friendly_name}</div>
                    <div class="incident-time">Status: DOWN • Last checked: ${new Date().toLocaleTimeString()}</div>
                </div>
            `).join('');
        }
        
        function updateServersTable(monitors) {
            const grouped = {};
            monitors.forEach(monitor => {
                const group = (monitor.friendly_name.split(" - ")[0] || "Other").trim();
                if (!grouped[group]) grouped[group] = [];
                grouped[group].push(monitor);
            });
            
            const tbody = document.getElementById('serversTableBody');
            tbody.innerHTML = '';
            
            for (const [serverGroup, serverList] of Object.entries(grouped)) {
                serverList.forEach((monitor, index) => {
                    const statusInfo = getStatusInfo(monitor.status);
                    const uptime = monitor.custom_uptime_ratio?.split('-')[0] || 'N/A';
                    const uptimeNum = parseFloat(uptime) || 0;
                    
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${index === 0 ? serverGroup : ''}</td>
                        <td>${monitor.friendly_name}</td>
                        <td><span class="status-badge ${statusInfo.class}">${statusInfo.text}</span></td>
                        <td>${uptime}%</td>
                        <td>
                            <div class="uptime-bar">
                                <div class="uptime-fill" style="width: ${uptimeNum}%"></div>
                            </div>
                            <small style="color: #a0a0a0;">${uptime}%</small>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            }
        }
        
        function fetchData() {
            fetch('/monitor/data')
                .then(response => response.json())
                .then(monitors => {
                    updateChart(monitors);
                    updateIncidents(monitors);
                    updateServersTable(monitors);
                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                });
        }
        
        // Initialize
        initChart();
        fetchData();
        
        // Update countdown and refresh data
        setInterval(() => {
            countdown--;
            document.getElementById('countdown').textContent = countdown;
            
            if (countdown === 0) {
                fetchData();
                countdown = 30;
                document.getElementById('countdown').textContent = countdown;
            }
        }, 1000);
    </script>
</body>
</html>