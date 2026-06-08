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
            title: "a) Clasificación de Texturas",
            file: "a-clasificadorTexturas/clasificador-texturas.py",
            runCmd: "python clasificador-texturas.py",
            demoType: "iframe",
            demoUrl: "a-clasificadorTexturas/index.html",
            installCmd: "pip install pillow numpy",
            isPython: true
        },
        suavizado: {
            title: "b) Filtro de Suavizado",
            file: "b-filtroSuavizado/filtro-suavizado.py",
            runCmd: "python filtro-suavizado.py",
            demoType: "iframe",
            demoUrl: "b-filtroSuavizado/index.html",
            installCmd: "pip install pillow numpy",
            isPython: true
        },
        vacalola: {
            title: "c) Cover \"La Vaca Lola\"",
            file: "c-vaca_lola/main.py",
            runCmd: "python main.py",
            demoType: "drive",
            demoUrl: "https://drive.google.com/file/d/1HniXizcHkK0GLqcvyKFEm3Zc0XMzTPgW/preview",
            driveViewUrl: "https://drive.google.com/file/d/1HniXizcHkK0GLqcvyKFEm3Zc0XMzTPgW/view?usp=drive_link",
            installCmd: "pip install opencv-python numpy",
            isPython: true
        },
        documentacion: {
            title: "📄 Informe Técnico",
            file: "",
            demoType: "info",
            demoUrl: "",
            isPython: false
        }
    };

    let currentProject = 'texturas';

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

        // Show/hide tabs according to project type
        const tabBtnCode = document.getElementById('tab-btn-code');
        const tabBtnSetup = document.getElementById('tab-btn-setup');
        const tabBtnExecute = document.getElementById('tab-btn-execute');

        if (data.isPython) {
            switchTab('code');
            tabBtnCode.style.display = 'inline-block';
            tabBtnSetup.style.display = 'inline-block';
            tabBtnExecute.style.display = 'inline-block';
            if (setupStepDeps) setupStepDeps.style.display = 'block';
            if (setupStepRun) setupStepRun.style.display = 'block';
        } else {
            // Documentacion: only show info in execute tab
            switchTab('execute');
            tabBtnCode.style.display = 'none';
            tabBtnSetup.style.display = 'none';
            tabBtnExecute.style.display = 'inline-block';
        }

        // Update install command dynamically
        const cmdInstallEl = document.getElementById('cmd-install');
        if (cmdInstallEl && data.installCmd) {
            cmdInstallEl.innerText = data.installCmd;
            // Update copy button for install cmd
            const copyBtn = cmdInstallEl.nextElementSibling;
            if (copyBtn) {
                copyBtn.onclick = () => navigator.clipboard.writeText(data.installCmd);
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
                    codeDisplay.textContent = "# Error: no se pudo cargar el archivo " + data.file;
                }
            } catch (err) {
                codeDisplay.textContent = "# Error de conexión al cargar el código.";
            }
            if (cmdRun) cmdRun.innerText = data.runCmd;
        }

        // Render Demo
        webDemoContainer.innerHTML = '';

        if (data.demoType === 'iframe') {
            const iframe = document.createElement('iframe');
            iframe.src = data.demoUrl;
            iframe.style.width = '100%';
            iframe.style.height = '100%';
            iframe.style.minHeight = '600px';
            iframe.style.border = 'none';
            iframe.allow = "autoplay";
            webDemoContainer.appendChild(iframe);

        } else if (data.demoType === 'drive') {
            const iframe = document.createElement('iframe');
            iframe.src = data.demoUrl;
            iframe.style.width = '100%';
            iframe.style.height = '100%';
            iframe.style.minHeight = '600px';
            iframe.style.border = 'none';
            iframe.allow = "autoplay";
            iframe.allowFullscreen = true;
            webDemoContainer.appendChild(iframe);

        } else if (data.demoType === 'info') {
            // Documentacion placeholder
            webDemoContainer.innerHTML = `
                <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;gap:1rem;padding:2rem;text-align:center;">
                    <div style="font-size:3rem;">📄</div>
                    <h3 style="color:#ffffff;font-size:1.4rem;">Informe Técnico</h3>
                    <p style="color:#64748b;max-width:400px;line-height:1.6;">
                        El informe técnico de este proyecto está disponible en formato PDF. 
                        Puedes solicitarlo directamente al autor.
                    </p>
                </div>`;
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
        if (data.demoType === 'drive') {
            // Open Drive video in new tab
            window.open(data.driveViewUrl, '_blank');
        } else if (data.file) {
            const link = document.createElement('a');
            link.href = data.file;
            link.download = data.file.split('/').pop();
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    };

    // Load initial project
    loadProject('texturas');
});
