<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Server Monitoring</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #1e1e2f;
            color: #e0e0e0;
            padding: 20px;
            height: 100vh;
            overflow-y: auto;
            scroll-behavior: smooth;
        }
        h2, h3 {
            color: #ffffff;
        }
        .server-block {
            background-color: #2c2c3e;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 0 8px rgba(0,0,0,0.3);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #444;
            text-align: left;
        }
        th {
            background-color: #3a3a4d;
        }
        .status-up { color: #28a745; font-weight: bold; }
        .status-down { color: #dc3545; font-weight: bold; }
        .status-paused { color: #ffc107; font-weight: bold; }
        .status-unknown { color: #6c757d; font-weight: bold; }
        .countdown-box {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #2c2c3e;
            border: 1px solid #444;
            padding: 10px 15px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
            z-index: 1000;
        }
        .countdown-box span {
            font-weight: bold;
            color: #00ffff;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <h2>🖥️ Server Monitoring</h2>
    <div class="countdown-box">
        Countdown: <span id="countdown">30</span>s
    </div>
    <div id="serverContainer"></div>

    <script>
        let countdown = 30;
        let scrollDown = true;
        let scrollStart = 0;
        let scrollEnd = 0;

        function smoothScroll(duration, from, to) {
            const startTime = performance.now();
            function scrollStep(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const easeInOut = progress < 0.5
                    ? 2 * progress * progress
                    : -1 + (4 - 2 * progress) * progress;
                const newPos = from + (to - from) * easeInOut;
                window.scrollTo(0, newPos);
                if (elapsed < duration) requestAnimationFrame(scrollStep);
            }
            requestAnimationFrame(scrollStep);
        }

        function scrollToDirection() {
            scrollStart = window.scrollY;
            scrollEnd = scrollDown ? document.body.scrollHeight - window.innerHeight : 0;
            smoothScroll(30000, scrollStart, scrollEnd);
        }

        function fetchData() {
            fetch('/monitor/data')
                .then(res => res.json())
                .then(monitors => {
                    const grouped = {};
                    monitors.forEach(m => {
                        const group = (m.friendly_name.split(" - ")[0] || "Lainnya").trim();
                        if (!grouped[group]) grouped[group] = [];
                        grouped[group].push(m);
                    });

                    const container = document.getElementById('serverContainer');
                    container.innerHTML = '';

                    for (const [server, list] of Object.entries(grouped)) {
                        const block = document.createElement('div');
                        block.className = 'server-block';
                        block.innerHTML = `
                            <h3>${server}</h3>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Nama Monitor</th>
                                        <th>Status</th>
                                        <th>Uptime 24 Jam</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${list.map(m => {
                                        let statusText = 'UNKNOWN';
                                        let statusClass = 'status-unknown';
                                        if (m.status === 2) {
                                            statusText = 'UP';
                                            statusClass = 'status-up';
                                        } else if (m.status === 8 || m.status === 9) {
                                            statusText = 'DOWN';
                                            statusClass = 'status-down';
                                        } else if (m.status === 0) {
                                            statusText = 'PAUSED';
                                            statusClass = 'status-paused';
                                        }
                                        return `
                                            <tr>
                                                <td>${m.friendly_name}</td>
                                                <td class="${statusClass}">${statusText}</td>
                                                <td>${(m.custom_uptime_ratio?.split('-')[0] || 'N/A')}%</td>
                                            </tr>
                                        `;
                                    }).join('')}
                                </tbody>
                            </table>
                        `;
                        container.appendChild(block);
                    }
                });
        }

        fetchData();
        scrollToDirection();

        setInterval(() => {
            countdown--;
            document.getElementById('countdown').textContent = countdown;
            if (countdown === 0) {
                fetchData();
                scrollDown = !scrollDown;
                scrollToDirection();
                countdown = 30;
                document.getElementById('countdown').textContent = countdown;
            }
        }, 1000);
    </script>
</body>
</html>
