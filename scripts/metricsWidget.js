// Variables used by Scriptable.
// icon-color: blue; icon-glyph: chart-line
// Script: Xusqui Metrics Widget

// Config
const METRICS_URL = 'https://xusqui.com/api/health'; // Health endpoint con métricas completas
const REFRESH_SECONDS = 60; // Tiempo recomendado para widget

// Entry
(async () => {
    try {
        const data = await fetchMetrics(METRICS_URL);
        const widget = await createWidget(data);
        if (config.runsInWidget) {
            Script.setWidget(widget);
        } else {
            await widget.presentMedium();
            Script.setWidget(widget);
        }
        Script.complete();
    } catch (err) {
        const w = new ListWidget();
        w.addText('Error obteniendo métricas');
        w.addSpacer();
        const t = w.addText(err.message || String(err));
        t.textColor = Color.red();
        if (!config.runsInWidget) await w.presentMedium();
        Script.complete();
    }
})();

// Fetch metrics
async function fetchMetrics(url) {
    const healthReq = new Request('https://xiro.xusqui.com/api/health');
    const metricsReq = new Request('https://xiro.xusqui.com/api/metrics');
    healthReq.timeoutInterval = 10;
    metricsReq.timeoutInterval = 10;

    const [health, metrics] = await Promise.all([
        healthReq.loadJSON(),
        metricsReq.loadJSON()
    ]);

    // Combinar ambos
    return { ...health, metrics };
}

// Helpers
function addHeader(stack, title) {
    const header = stack.addText(title);
    header.font = Font.boldSystemFont(14);
    header.textColor = Color.white();
}

function addKeyValue(stack, key, value, valueColor) {
    const row = stack.addStack();
    row.layoutHorizontally();
    const k = row.addText(key);
    k.font = Font.semiboldSystemFont(12);
    k.textColor = Color.white();
    row.addSpacer();
    const v = row.addText(value);
    v.font = Font.systemFont(12);
    v.textColor = valueColor || Color.white();
}

function formatBytes(mb) {
    return `${mb} MB`;
}

function formatUptime(seconds) {
    if (!seconds) return '0s';
    const d = Math.floor(seconds / 86400);
    const h = Math.floor((seconds % 86400) / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    if (d > 0) return `${d}d ${h}h`;
    if (h > 0) return `${h}h ${m}m`;
    return `${m}m`;
}

async function createWidget(metrics) {
    const w = new ListWidget();
    const bg = new LinearGradient();
    bg.locations = [0, 1];
    bg.colors = [new Color('#131a2b'), new Color('#0b0f1a')];
    w.backgroundGradient = bg;
    w.setPadding(30, 8, 4, 8);

    // Header compacto en una línea
    const header = w.addStack();
    header.layoutHorizontally();
    header.centerAlignContent();
    const title = header.addText('🎮 XIRO');
    title.font = Font.boldSystemFont(12);
    title.textColor = Color.white();
    header.addSpacer(4);
    const updated = header.addText(new Date(metrics.timestamp || Date.now()).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' }));
    updated.font = Font.systemFont(8);
    updated.textColor = new Color('#9ca3af');

    w.addSpacer(1);

    // Tarjetas compactas (2 filas x 4 columnas)
    function addCard(parent, titleText, icon) {
        const card = parent.addStack();
        card.layoutVertically();
        card.backgroundColor = new Color('#1f2937', 0.55);
        card.cornerRadius = 6;
        card.setPadding(1.2, 2.4, 1.2, 2.4);
        const t = card.addText(icon);
        t.font = Font.systemFont(9);
        card.addSpacer(0.2);
        const title = card.addText(titleText);
        title.font = Font.semiboldSystemFont(6);
        title.textColor = new Color('#9ca3af');
        card.addSpacer(0.4);
        return card;
    }

    function addCompactKV(stack, key, value, highlight = false) {
        const row = stack.addStack();
        row.layoutHorizontally();
        const k = row.addText(key);
        k.font = Font.systemFont(7);
        k.textColor = new Color('#6b7280');
        row.addSpacer();
        const v = row.addText(value);
        v.font = Font.semiboldMonospacedSystemFont(highlight ? 8 : 7);
        v.textColor = highlight ? new Color('#60a5fa') : Color.white();
    }

    // Fila 1
    const row1 = w.addStack();
    row1.layoutHorizontally();
    row1.spacing = 4;

    const sysCard = addCard(row1, 'System', '⚙️');
    addCompactKV(sysCard, 'Up', formatUptime(metrics.uptime));
    addCompactKV(sysCard, 'RAM', `${metrics.memory?.heap?.usedMB}MB`);

    const httpCard = addCard(row1, 'HTTP', '📡');
    addCompactKV(httpCard, 'Req/m', String(metrics.metrics?.http?.requests_per_minute || 0), true);
    addCompactKV(httpCard, 'Total', String(metrics.metrics?.http?.total_requests || 0));

    const playersCard = addCard(row1, 'Players', '👥');
    const activePlayers = metrics.players?.connected || 0;
    const pText = playersCard.addText(String(activePlayers));
    pText.font = Font.boldSystemFont(12);
    pText.textColor = new Color('#34d399');
    playersCard.addSpacer(0.2);
    addCompactKV(playersCard, 'Total', String(metrics.metrics?.players?.total_connections_ever || 0));

    const dbCard = addCard(row1, 'Database', '🗄️');
    const dbStatus = metrics.database?.status || 'unknown';
    const dbStatusText = dbCard.addText(dbStatus === 'healthy' ? '✓ OK' : dbStatus === 'slow' ? '⚠ Slow' : '✗ Error');
    dbStatusText.font = Font.boldSystemFont(9);
    dbStatusText.textColor = dbStatus === 'healthy' ? new Color('#34d399') : dbStatus === 'slow' ? new Color('#f59e0b') : new Color('#ef4444');
    dbCard.addSpacer(0.2);
    addCompactKV(dbCard, 'Lat', `${metrics.database?.latencyMs || 0}ms`, metrics.database?.latencyMs > 50);
    addCompactKV(dbCard, 'Q Avg', `${metrics.metrics?.database?.avg_query_time_ms || 0}ms`, metrics.metrics?.database?.avg_query_time_ms > 50);

    w.addSpacer(1);

    // Fila 2
    const row2 = w.addStack();
    row2.layoutHorizontally();
    row2.spacing = 4;

    const gamesCard = addCard(row2, 'Games', '🎯');
    const activeGames = metrics.games?.active || 0;
    const gText = gamesCard.addText(String(activeGames));
    gText.font = Font.boldSystemFont(12);
    gText.textColor = new Color('#f59e0b');
    gamesCard.addSpacer(0.2);
    addCompactKV(gamesCard, 'Total', String(metrics.metrics?.games?.total_created || 0));

    const cacheCard = addCard(row2, 'Cache', '💾');
    const hitRate = parseFloat((metrics.cache?.questionBanks?.hitRate || '0%').replace('%', ''));
    const hitText = cacheCard.addText(`${hitRate.toFixed(0)}%`);
    hitText.font = Font.boldSystemFont(12);
    hitText.textColor = hitRate > 70 ? new Color('#34d399') : hitRate > 40 ? new Color('#f59e0b') : new Color('#ef4444');
    cacheCard.addSpacer(0.2);
    addCompactKV(cacheCard, 'Hits', String(metrics.cache?.questionBanks?.hits || 0));

    const poolCard = addCard(row2, 'Pool', '🔌');
    const poolIdle = metrics.database?.pool?.idle || 0;
    const poolTotal = metrics.database?.pool?.total || 0;
    addCompactKV(poolCard, 'Idle', `${poolIdle}/${poolTotal}`);
    addCompactKV(poolCard, 'Wait', String(metrics.database?.pool?.waiting || 0));

    const lobbiesCard = addCard(row2, 'Lobbies', '🚪');
    const activeLobbies = metrics.games?.lobbies || 0;
    const lobText = lobbiesCard.addText(String(activeLobbies));
    lobText.font = Font.boldSystemFont(12);
    lobText.textColor = new Color('#8b5cf6');
    lobbiesCard.addSpacer(0.2);
    addCompactKV(lobbiesCard, 'Max', String(metrics.games?.maxLobbies || 10));

    w.addSpacer(1);

    return w;
}

// End of file
