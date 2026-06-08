document.addEventListener('DOMContentLoaded', () => {
    const navItems = document.querySelectorAll('.nav-item');
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    const projectTitle = document.getElementById('project-title');
    const codeDisplay = document.getElementById('code-display');
    const webDemoContainer = document.getElementById('web-demo-container');
    const cmdRun = document.getElementById('cmd-run');
    
    const setupStepDeps = document.getElementById('setup-step-deps');
    const setupStepRun = document.getElementById('setup-step-run');
    
    const projectData = {
        texturas: {
            title: "Clasificación de Texturas",
            file: "a-clasficacionTexturas/Texturas.py",
            runCmd: "python Texturas.py",
            demoType: "iframe",
            demoUrl: "a-clasficacionTexturas/index.html",
            isPython: true
        },
        suavizado: {
            title: "Filtro de Suavizado",
            file: "b-filtrosuavizado/Suavizado.py",
            runCmd: "python Suavizado.py",
            demoType: "iframe",
            demoUrl: "b-filtrosuavizado/index.html",
            isPython: true
        },
        vacalola: {
            title: "Cover \"La Vaca Lola\"",
            file: "", // No python code for vacalola
            demoType: "video",
            // Google Drive preview URL is acceptable in iframe, or we can use direct video tag if we have the file. 
            // We use iframe pointing to drive preview as requested by user.
            demoUrl: "https://drive.google.com/file/d/1srPh7fezOkYW4nOA8_nesZiHU33uep2j/preview",
            isPython: false
        },
        documentacion: {
            title: "Documentación - Informe Técnico",
            file: "informe_tecnico_final.pdf",
            demoType: "pdf",
            demoUrl: "informe_tecnico_final.pdf",
            isPython: false
        }
    };

    let currentProject = 'texturas';

    // To prevent tab bugs, we manage state explicitly
    function switchTab(tabId) {
        tabBtns.forEach(btn => btn.classList.remove('active'));
        tabContents.forEach(content => content.classList.remove('active'));
        
        const targetBtn = document.querySelector(`.tab-btn[data-tab="${tabId}"]`);
        const targetContent = document.getElementById(`${tabId}-tab`);
        
        if (targetBtn) targetBtn.classList.add('active');
        if (targetContent) targetContent.classList.add('active');
    }

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            switchTab(btn.dataset.tab);
        });
    });

    async function loadProject(projectId) {
        const data = projectData[projectId];
        projectTitle.innerText = data.title;
        currentProject = projectId;
        
        // Always reset to code tab on project change to avoid confusion (fixing the bug the user reported)
        if (data.isPython) {
            switchTab('code');
            document.getElementById('tab-btn-code').style.display = 'inline-block';
            document.getElementById('tab-btn-setup').style.display = 'inline-block';
            setupStepDeps.style.display = 'block';
            setupStepRun.style.display = 'block';
        } else {
            // For Vacalola & PDF, show "Ver ejecucion en web" immediately, hide Code/Setup if not applicable
            switchTab('execute');
            document.getElementById('tab-btn-code').style.display = 'none';
            if (projectId === 'documentacion') {
                document.getElementById('tab-btn-setup').style.display = 'inline-block';
                setupStepDeps.style.display = 'none';
                setupStepRun.style.display = 'none';
            } else {
                document.getElementById('tab-btn-setup').style.display = 'none';
            }
        }

        // Fetch Source Code if python
        if (data.isPython && data.file) {
            try {
                const response = await fetch(data.file);
                if (response.ok) {
                    const code = await response.text();
                    codeDisplay.textContent = code;
                    Prism.highlightElement(codeDisplay);
                } else {
                    codeDisplay.textContent = "# Error cargando el código fuente";
                }
            } catch (err) {
                codeDisplay.textContent = "# Error de conexión al cargar código.";
            }
            if (cmdRun) cmdRun.innerText = data.runCmd;
        }

        // Render Demo (Web iframe or video or PDF)
        webDemoContainer.innerHTML = '';
        if (data.demoType === 'iframe' || data.demoType === 'video') {
            const iframe = document.createElement('iframe');
            iframe.src = data.demoUrl;
            iframe.style.width = '100%';
            iframe.style.height = '100%';
            iframe.style.border = 'none';
            iframe.allow = "autoplay";
            webDemoContainer.appendChild(iframe);
        } else if (data.demoType === 'pdf') {
            const embed = document.createElement('embed');
            embed.src = data.demoUrl;
            embed.type = "application/pdf";
            embed.style.width = '100%';
            embed.style.height = '600px';
            webDemoContainer.appendChild(embed);
        }
    }

    navItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            navItems.forEach(nav => nav.classList.remove('active'));
            item.classList.add('active');
            loadProject(item.dataset.project);
        });
    });

    // Global download function
    window.downloadCurrentScript = function() {
        const data = projectData[currentProject];
        if (data.file) {
            const link = document.createElement('a');
            link.href = data.file;
            link.download = data.file.split('/').pop();
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } else if (currentProject === 'vacalola') {
            window.open('https://drive.google.com/drive/folders/1ghSZ3wi3zvLkjOkjbb3x4_BTbjmzHhUB?usp=drive_link', '_blank');
        }
    };

    // Load initial project
    loadProject('texturas');
});
