<?php
// 1. CONFIGURACIÓN DE ARCHIVOS
$f = 'publicidad.json';
if (!file_exists($f)) { file_put_contents($f, json_encode([])); }
$json_data = file_get_contents($f);
$d = json_decode($json_data, true);
if (!is_array($d)) { $d = []; }

// 2. PROCESAMIENTO DE DATOS (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // AÑADIR NUEVO (Se usa array_unshift para que los nuevos queden arriba)
    if (isset($_POST['n']) && !isset($_POST['edit_index'])) {
        array_unshift($d, [
            'cliente' => strtoupper(trim($_POST['n'])), 
            'url' => trim($_POST['u'])
        ]);
    } 
    // ELIMINAR
    elseif (isset($_POST['e'])) {
        $idx = intval($_POST['e']);
        if (isset($d[$idx])) {
            array_splice($d, $idx, 1);
        }
    } 
    // EDITAR EXISTENTE
    elseif (isset($_POST['edit_index'])) {
        $idx = intval($_POST['edit_index']);
        if (isset($d[$idx])) {
            $d[$idx]['cliente'] = strtoupper(trim($_POST['nuevo_nombre']));
            $d[$idx]['url'] = trim($_POST['nueva_url']);
        }
    }
    
    file_put_contents($f, json_encode($d, JSON_UNESCAPED_UNICODE));
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Publicidad | Alcalá Fiesta</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Inter', sans-serif;
            background: #0f172a; 
            background-image: radial-gradient(at top right, #1e1b4b 0%, #0f172a 100%); 
            min-height: 100vh; 
        }
        .custom-scroll::-webkit-scrollbar { width: 4px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: rgba(168, 85, 247, 0.4); border-radius: 10px; }
        .client-card.hidden-search { display: none !important; }
        .bad-link { border-color: #ef4444 !important; background: rgba(239, 68, 68, 0.1) !important; }
    </style>
</head>
<body class="text-white p-4 flex justify-center">

    <div class="w-full max-w-xl bg-slate-800/40 backdrop-blur-xl p-6 rounded-[2.5rem] border border-white/10 shadow-2xl my-4">
        
        <div class="text-center mb-8">
            <h2 class="text-2xl font-black text-purple-400 uppercase tracking-tighter italic">Panel de Cuñas</h2>
            <p class="text-slate-400 text-[10px] font-bold tracking-[3px] uppercase">Alcalá Fiesta | Humberto Posso</p>
        </div>

        <form method="POST" class="space-y-4 mb-8 bg-slate-900/60 p-5 rounded-3xl border border-white/5 shadow-inner">
            <h3 class="text-[10px] font-black text-purple-300 uppercase ml-2 tracking-widest">Registrar Nuevo Cliente</h3>
            
            <input type="text" name="n" placeholder="NOMBRE DEL COMERCIO (EJ: MONTAGAS)" 
                class="w-full p-4 rounded-2xl bg-slate-950 border border-white/10 text-white focus:ring-2 focus:ring-purple-500 outline-none text-sm font-bold uppercase" required>
            
            <div>
                <input type="url" name="u" placeholder="LINK DE LA IMAGEN (EJ: https://i.imgur.com/...)" 
                    class="w-full p-4 rounded-2xl bg-slate-950 border border-white/10 text-white focus:ring-2 focus:ring-purple-500 outline-none text-sm" required>
                <p class="text-[9px] text-slate-500 ml-3 mt-1 tracking-wider">💡 Sugerencia: Usa un enlace directo de imagen (Imgur, etc.)</p>
            </div>

            <button type="submit" class="w-full bg-purple-600 hover:bg-purple-500 p-4 rounded-2xl font-black text-xs transition-all uppercase tracking-widest shadow-lg shadow-purple-900/20 active:scale-95">
                Guardar Publicidad
            </button>
        </form>

        <div class="relative mb-6">
            <input type="text" id="searchInput" onkeyup="searchClients()" placeholder="BUSCAR CLIENTE POR NOMBRE..." 
                class="w-full p-4 pl-12 rounded-2xl bg-slate-900/40 border border-white/5 text-white focus:ring-2 focus:ring-purple-500 outline-none text-[10px] font-black uppercase tracking-widest">
            <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-500"></i>
        </div>

        <div class="space-y-4">
            <div class="flex justify-between items-center px-2">
                <h3 class="text-[10px] font-black text-slate-500 uppercase tracking-[3px]">Clientes en Sistema</h3>
                <span id="total-badge" class="bg-purple-600/20 text-purple-400 text-[10px] font-bold px-3 py-1 rounded-full border border-purple-500/30">
                    <?php echo count($d); ?> TOTAL
                </span>
            </div>

            <div id="clientsList" class="grid gap-3 max-h-[500px] overflow-y-auto pr-2 custom-scroll">
                <?php if (empty($d)): ?>
                    <div class="text-center py-10 bg-slate-900/30 rounded-2xl border border-white/5">
                        <i class="fas fa-folder-open text-slate-600 text-2xl mb-2"></i>
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">No hay publicidad registrada</p>
                        <p class="text-[10px] text-slate-600 mt-1">Agrega tu primer cliente comercial arriba.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($d as $i => $a): ?>
                    <div id="card-<?php echo $i; ?>" class="client-card bg-slate-900/60 p-3 rounded-2xl border border-white/5 transition-all">
                        <div class="flex items-center w-full">
                            
                            <div class="w-12 h-12 rounded-xl overflow-hidden flex-shrink-0 border border-white/10 bg-black relative shadow-lg">
                                <img src="<?php echo htmlspecialchars($a['url']); ?>" 
                                     class="w-full h-full object-cover" 
                                     onerror="markAsBroken(<?php echo $i; ?>)">
                            </div>

                            <div class="ml-4 flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="client-name font-bold text-xs text-slate-100 uppercase tracking-tight truncate">
                                        <?php echo htmlspecialchars($a['cliente']); ?>
                                    </p>
                                    <span id="status-<?php echo $i; ?>" class="w-2 h-2 rounded-full bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.6)]"></span>
                                </div>
                                <p id="msg-<?php echo $i; ?>" class="text-[9px] text-purple-400/50 truncate font-mono mt-0.5"><?php echo htmlspecialchars($a['url']); ?></p>
                            </div>

                            <div class="flex gap-2 ml-4 flex-shrink-0">
                                <button onclick="openEdit(<?php echo $i; ?>)" class="w-9 h-9 flex items-center justify-center rounded-xl bg-blue-500/10 text-blue-400 hover:bg-blue-500 hover:text-white transition-all">
                                    <i class="fas fa-edit text-[10px]"></i>
                                </button>
                                <form method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este comercial?');" class="m-0">
                                    <input type="hidden" name="e" value="<?php echo $i; ?>">
                                    <button class="w-9 h-9 flex items-center justify-center rounded-xl bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white transition-all">
                                        <i class="fas fa-trash-alt text-[10px]"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div id="edit-box-<?php echo $i; ?>" class="hidden mt-4 pt-4 border-t border-white/5">
                            <form method="POST" class="space-y-2">
                                <input type="hidden" name="edit_index" value="<?php echo $i; ?>">
                                <input type="text" name="nuevo_nombre" value="<?php echo htmlspecialchars($a['cliente']); ?>" class="w-full p-3 bg-slate-800 border border-white/10 rounded-xl text-xs uppercase font-bold text-white outline-none focus:ring-1 focus:ring-blue-500">
                                <input type="url" name="nueva_url" value="<?php echo htmlspecialchars($a['url']); ?>" class="w-full p-3 bg-slate-800 border border-white/10 rounded-xl text-[10px] text-slate-400 outline-none focus:ring-1 focus:ring-blue-500">
                                <div class="flex gap-2">
                                    <button type="submit" class="flex-grow bg-blue-600 p-2 rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg active:scale-95">Actualizar Datos</button>
                                    <button type="button" onclick="openEdit(<?php echo $i; ?>)" class="bg-slate-700 p-2 rounded-xl text-[10px] px-4 font-black">X</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-8 text-center">
            <a href="index.html" class="text-[9px] font-bold text-slate-500 hover:text-purple-400 transition-all uppercase tracking-[4px] italic">
                <i class="fas fa-arrow-left mr-2"></i> Volver al Reproductor
            </a>
        </div>
    </div>

    <script>
        function markAsBroken(id) {
            const card = document.getElementById('card-' + id);
            const status = document.getElementById('status-' + id);
            const msg = document.getElementById('msg-' + id);
            
            if(card && status && msg) {
                card.classList.add('bad-link');
                status.classList.replace('bg-green-500', 'bg-red-500');
                status.classList.add('animate-pulse');
                status.style.boxShadow = "0 0 12px rgba(239, 68, 68, 0.8)";
                
                msg.innerText = "⚠️ ERROR: IMAGEN NO ENCONTRADA O LINK ROTO";
                msg.classList.replace('text-purple-400/50', 'text-red-500');
                msg.classList.add('font-bold');
            }
        }

        function searchClients() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toUpperCase();
            const cards = document.getElementsByClassName('client-card');
            let found = 0;

            for (let i = 0; i < cards.length; i++) {
                const name = cards[i].querySelector('.client-name').innerText;
                if (name.toUpperCase().indexOf(filter) > -1) {
                    cards[i].classList.remove('hidden-search');
                    found++;
                } else {
                    cards[i].classList.add('hidden-search');
                }
            }
            const badge = document.getElementById('total-badge');
            if(badge) badge.innerText = found + " RESULTADOS";
        }

        function openEdit(id) {
            const box = document.getElementById('edit-box-' + id);
            if(box) box.classList.toggle('hidden');
        }
    </script>
</body>
</html>
