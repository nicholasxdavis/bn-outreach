<?php require_once '../config.php'; ?>
<!DOCTYPE html>
<html lang="en">
   <head>
          
      <meta charset="UTF-8">
          
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
          
      <title>Blacnova - Outreach Tool</title>
          
      <link rel="icon" href="https://www.blacnova.net/img/bn_orange.png" type="image/png">
          <script src="https://cdn.tailwindcss.com"></script>
          
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
          
      <link rel="preconnect" href="https://fonts.googleapis.com">
          
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
          
      <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
          
      <style>
                 /* Blacnova Inspired UI & Dark Theme */
                 :root {
                     --primary: #ea580c;
                     --primary-light: #f97316;
                     --dark-bg: #0a0a0a;
                     --card-bg: #101010;
                     --border-color: rgba(255, 255, 255, 0.1);
                     --text-primary: #f3f4f6;
                     --text-secondary: #9ca3af;
                 }
                 body {
                     background-color: #fff; /* Default light background */
                     font-family: 'Inter', sans-serif;
                     -webkit-font-smoothing: antialiased;
                     -moz-osx-font-smoothing: grayscale;
                     position: relative;
                     overflow-x: hidden;
                     transition: background-color 0.3s ease, color 0.3s ease;
                 }
                 html.dark body {
                     background-color: #101010;
                 }
                 
                 /* Keep text inside app white/light-gray regardless of theme */
                 body {
                      color: var(--text-primary);
                 }
                 html:not(.dark) .text-white {
                      color: var(--text-primary);
                 }
                 html:not(.dark) .text-gray-400 {
                     color: var(--text-secondary);
                 }
                 html:not(.dark) #auth-modal .text-white {
                     color: var(--text-primary) !important;
                 }
                 html:not(.dark) #auth-modal .text-gray-400 {
                     color: var(--text-secondary) !important;
                 }
                 html:not(.dark) .glass-card,
                 html:not(.dark) input,
                 html:not(.dark) select,
                 html:not(.dark) textarea {
                     color: var(--text-primary);
                 }
                 html:not(.dark) .btn-secondary {
                     color: var(--text-primary);
                 }
                 html:not(.dark) .btn-secondary.active {
                     color: white;
                 }
                 /* General Styles */
                 .glass-card {
                     background-color: var(--card-bg);
                     backdrop-filter: blur(10px);
                     -webkit-backdrop-filter: blur(10px);
                     border: 1px solid var(--border-color);
                 }
                 .btn-primary {
                     background-color: var(--primary);
                     color: white;
                     transition: all 0.2s ease-in-out;
                     border-radius: 0.75rem;
                 }
                 .btn-primary:hover {
                     background-color: var(--primary-light);
                     transform: translateY(-2px);
                     box-shadow: 0 4px 15px rgba(234, 88, 12, 0.3);
                 }
                 .btn-primary:disabled {
                     background-color: #555;
                     cursor: not-allowed;
                     transform: none;
                     box-shadow: none;
                 }
                 .btn-secondary {
                     background-color: #1a1a1a;
                     border: 1px solid var(--border-color);
                     color: var(--text-primary);
                     transition: all 0.2s ease-in-out;
                     border-radius: 0.75rem;
                 }
                 .btn-secondary:hover {
                     background-color: #2a2a2a;
                     border-color: var(--primary);
                 }
                 .btn-secondary.active {
                     background-color: var(--primary);
                     border-color: var(--primary);
                     color: white;
                     box-shadow: 0 0 10px rgba(234, 88, 12, 0.3);
                 }
                 /* --- Authentication Styles --- */
                 #main-content {
                     display: none;
                     opacity: 0;
                     transition: opacity 0.5s ease-in-out;
                 }
                 @keyframes shake {
                     10%, 90% { transform: translate3d(-1px, 0, 0); }
                     20%, 80% { transform: translate3d(2px, 0, 0); }
                     30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
                     40%, 60% { transform: translate3d(4px, 0, 0); }
                 }
                 .shake {
                     animation: shake 0.82s cubic-bezier(.36,.07,.19,.97) both;
                 }
                 /* --- Styles for Pattern Background --- */
                 .pattern-container {
                     position: fixed;
                     top: 0;
                     left: 0;
                     width: 100%;
                     height: 100vh;
                     z-index: -1;
                     overflow: hidden;
                     pointer-events: none;
                     mask-image: linear-gradient( to bottom, transparent 0%, black 15%, black 85%, transparent 100% );
                     -webkit-mask-image: linear-gradient( to bottom, transparent 0%, black 15%, black 85%, transparent 100% );
                 }
                 .pattern-icon {
                     position: absolute;
                     display: block;
                     width: 80px;
                     height: 80px;
                     background-size: contain;
                     background-repeat: no-repeat;
                     will-change: transform, opacity;
                     transition: background-image 0.3s ease;
                 }
                 html.dark .pattern-icon {
                     background-image: url('../img/icon_dark.png');
                 }
                 html:not(.dark) .pattern-icon {
                      background-image: url('../img/icon.png');
                 }
                 /* --- Specific styles for Reacher AI --- */
                 input, select, textarea {
                     background-color: #1a1a1a;
                     border: 1px solid var(--border-color);
                     border-radius: 0.75rem;
                     color: var(--text-primary);
                     transition: all 0.2s ease;
                 }
                 input:focus, select:focus, textarea:focus {
                     outline: none;
                     border-color: var(--primary);
                     box-shadow: 0 0 0 3px rgba(212, 97, 28, 0.3);
                 }
                 .page { display: none; }
                 .page.active { display: block; }
                 
                 #toast {
                     position: fixed;
                     bottom: -100px;
                     left: 50%;
                     transform: translateX(-50%);
                     background-color: var(--primary);
                     color: white;
                     padding: 1rem 1.5rem;
                     border-radius: 0.75rem;
                     box-shadow: 0 4px 20px rgba(0,0,0,0.4);
                     transition: bottom 0.5s ease-in-out;
                     z-index: 100;
                 }
                 #toast.show { bottom: 20px; }
                 .loader {
                     width: 1.25rem;
                     height: 1.25rem;
                     border: 2px solid #FFF;
                     border-bottom-color: transparent;
                     border-radius: 50%;
                     display: inline-block;
                     box-sizing: border-box;
                     animation: rotation 1s linear infinite;
                 }
                 @keyframes rotation {
                     0% { transform: rotate(0deg); }
                     100% { transform: rotate(360deg); }
                 }
                 ::-webkit-scrollbar { width: 8px; }
                 ::-webkit-scrollbar-track { background: #1a1a1a; }
                 ::-webkit-scrollbar-thumb { background: #444; border-radius: 4px; }
                 ::-webkit-scrollbar-thumb:hover { background: #555; }
             
      </style>
   </head>
   <body class="min-h-screen">
              
      <div id="auth-overlay" class="fixed inset-0 z-50 flex items-center justify-center bg-black backdrop-blur-sm" style="display: none;">
                 
         <div id="auth-modal" class="glass-card p-8 rounded-2xl w-full max-w-sm">
                         
            <div class="flex justify-center mb-4">
                                <img src="https://www.blacnova.net/img/bn_orange.png" alt="Blacnova Logo" class="h-12 w-12 object-contain">
                            
            </div>
                         
            <h2 class="text-2xl font-bold text-center text-white mb-2">Restricted Access</h2>
                         
            <p class="text-center text-gray-400 mb-6">Please enter your credentials to continue.</p>
                         
            <form id="login-form">
                                
               <div class="mb-4">
                                       <label for="username" class="block text-sm font-medium text-gray-400 mb-2">User</label>
                                       <input type="text" id="username" class="w-full bg-[#1a1a1a] border border-gray-700 rounded-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-primary transition text-white" required>
                                   
               </div>
                                
               <div class="mb-6">
                                       <label for="passcode" class="block text-sm font-medium text-gray-400 mb-2">Passcode</label>
                                       <input type="password" id="passcode" class="w-full bg-[#1a1a1a] border border-gray-700 rounded-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-primary transition text-white" required>
                                   
               </div>
                                <button type="submit" class="w-full btn-primary font-bold py-2 px-4 rounded-lg">
                                    Login
                                </button>
                                
               <p id="auth-error" class="text-red-500 text-sm text-center mt-4 hidden">Invalid credentials. Please try again.</p>
                            
            </form>
                    
         </div>
              
      </div>
              
      <div id="main-content">
                         
         <header class="sticky top-0 z-40 w-full glass-card">
                        
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                               
               <div class="flex items-center justify-between py-4">
                                      
                  <div class="flex items-center space-x-4">
                                             <img src="https://www.blacnova.net/img/bn.png" alt="Blacnova Logo" class="h-8 sm:h-14">
                                                                     
                     <h1 class="text-xl md:text-2xl font-light text-white hidden sm:block">Outreach</h1>
                                         
                  </div>
                                      
                  <div class="flex items-center space-x-4">
                                              <a href="../index.php" class="text-gray-400 hover:text-white transition-colors duration-200 text-sm sm:text-base mr-4">Business Index</a>
                                             <button id="theme-toggle-btn" class="text-gray-400 hover:text-white transition-colors duration-200">
                                                 <i class="fas fa-moon text-lg"></i>
                                             </button>
                                         
                  </div>
                                  
               </div>
                           
            </div>
                    
         </header>
                         
         <main class="container mx-auto p-4 sm:p-6 lg:p-8">
                                     
            <div class="mb-8 p-4 glass-card rounded-2xl">
                                
               <div id="nav-tabs" class="flex flex-wrap gap-3">
                                        <button class="nav-tab btn-secondary active px-4 py-2 text-sm rounded-full" data-page="draft">
                                            <i class="fas fa-magic mr-2"></i>Email Draft
                                        </button>
                                        <button class="nav-tab btn-secondary px-4 py-2 text-sm rounded-full" data-page="log">
                                            <i class="fas fa-history mr-2"></i>Outreach Log
                                        </button>
                                        <button class="nav-tab btn-secondary px-4 py-2 text-sm rounded-full" data-page="settings">
                                            <i class="fas fa-cog mr-2"></i>Settings
                                        </button>
                                   
               </div>
                            
            </div>
                                    
            <div id="draft-page" class="page active">
                               
               <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                                          
                  <div class="glass-card p-6 rounded-2xl">
                                             
                     <h2 class="text-xl font-semibold mb-6">1. Enter Prospect Details</h2>
                                             
                     <form id="draft-form" class="space-y-6">
                                                    
                        <div>
                                                           <label for="business-name" class="block text-sm font-medium text-text-secondary mb-2">Business Name</label>
                                                           <input type="text" id="business-name" placeholder="e.g., La Nueva Casita Café" required class="w-full px-4 py-3">
                                                       
                        </div>
                                                    
                        <div>
                                                           <label for="recipient-email" class="block text-sm font-medium text-text-secondary mb-2">Recipient Email</label>
                                                           <input type="email" id="recipient-email" placeholder="e.g., contact@business.com" required class="w-full px-4 py-3">
                                                       
                        </div>
                                                    
                        <div>
                                                           <label for="website-status" class="block text-sm font-medium text-text-secondary mb-2">Website Status</label>
                                                           
                           <select id="website-status" required class="w-full px-4 py-3">
                                                                  
                              <option value="no-website">No Website</option>
                                                                  
                              <option value="outdated">Existing, but Outdated</option>
                                                                  
                              <option value="competitor-platform">On Squarespace/Wix/etc.</option>
                                                                  
                              <option value="bad-ux">Hard to Navigate / Bad UX</option>
                                                              
                           </select>
                                                       
                        </div>
                                                    
                        <div>
                                                            <label for="reason" class="block text-sm font-medium text-text-secondary mb-2">Primary Reason for Contact (Optional)</label>
                                                            <textarea id="reason" rows="3" placeholder="e.g., To build a modern, easy-to-manage site." class="w-full px-4 py-3"></textarea>
                                                       
                        </div>
                                                    
                        <div>
                                                           <button type="submit" id="generate-btn" class="w-full btn-primary px-5 py-3 text-base flex items-center justify-center gap-2">
                                                               <span class="btn-text">✨ Generate with AI</span>
                                                               <span class="loader hidden"></span>
                                                           </button>
                                                       
                        </div>
                                                
                     </form>
                                         
                  </div>
                                                          
                  <div class="glass-card p-6 rounded-2xl">
                                             
                     <h2 class="text-xl font-semibold mb-6">2. Review & Use Email</h2>
                                             
                     <div id="output-container" class="space-y-6 hidden">
                                                    
                        <div>
                                                           <label class="block text-sm font-medium text-text-secondary mb-2">Subject</label>
                                                           
                           <div class="relative">
                                                                  <textarea id="output-subject" class="w-full px-4 py-3 pr-12 bg-dark-bg"></textarea>
                                                                  <button class="absolute top-1/2 right-3 -translate-y-1/2 text-text-secondary hover:text-primary" onclick="copyToClipboard('output-subject')">
                                                                      <i class="fas fa-copy"></i>
                                                                  </button>
                                                              
                           </div>
                                                       
                        </div>
                                                    
                        <div>
                                                           <label class="block text-sm font-medium text-text-secondary mb-2">Body</label>
                                                           
                           <div class="relative">
                                                                  <textarea id="output-body" rows="8" class="w-full px-4 py-3 pr-12 bg-dark-bg"></textarea>
                                                                  <button class="absolute top-3 right-3 text-text-secondary hover:text-primary" onclick="copyToClipboard('output-body')">
                                                                      <i class="fas fa-copy"></i>
                                                                  </button>
                                                              
                           </div>
                                                       
                        </div>
                                                    
                                                    <button id="send-outreach-btn" disabled class="w-full btn-primary px-5 py-3 text-base flex items-center justify-center gap-2">
                                                         <span class="btn-text"><i class="fas fa-paper-plane mr-2"></i>Send with Zoho & Log</span>
                                                         <span class="loader hidden"></span>
                                                    </button>
                                                    <button id="save-outreach-btn" disabled class="w-full btn-secondary px-5 py-3 text-base">
                                                        <i class="fas fa-save mr-2"></i>Log Manually
                                                    </button>
                                                
                     </div>
                                             
                     <div id="placeholder-output" class="text-center text-text-secondary py-20">
                                                    <i class="fas fa-envelope-open-text fa-3x mb-4"></i>
                                                    
                        <p>Your AI-generated email will appear here.</p>
                                                
                     </div>
                                         
                  </div>
                                  
               </div>
                           
            </div>
                                    
            <div id="log-page" class="page">
                               
               <div class="glass-card overflow-hidden rounded-2xl">
                                      
                  <div id="log-table-container" class="overflow-x-auto">
                                             
                     <table class="w-full text-left">
                                                    
                        <thead class="border-b border-border-color">
                                                           
                           <tr>
                                                                  
                              <th class="px-6 py-4 font-semibold">Business</th>
                                                                  
                              <th class="px-6 py-4 font-semibold">Subject</th>
                                                                  
                              <th class="px-6 py-4 font-semibold">Date</th>
                                                                  
                              <th class="px-6 py-4 font-semibold">Status</th>
                                                              
                           </tr>
                                                       
                        </thead>
                                                    
                        <tbody id="log-table-body">
                                                                                       
                        </tbody>
                                                
                     </table>
                                         
                  </div>
                                                          
                  <div id="empty-log-placeholder" class="hidden text-center text-text-secondary py-20 px-6">
                                             <i class="fas fa-history fa-3x mb-4 text-gray-600"></i>
                                             
                     <h3 class="mt-2 text-lg font-medium text-text-primary">No Outreach Logged</h3>
                                             
                     <p class="mt-1 text-sm">Generate and save an email to see your history.</p>
                                         
                  </div>
                                  
               </div>
                           
            </div>
                                     
            <div id="settings-page" class="page">
                                  
               <div class="glass-card p-6 max-w-lg mx-auto rounded-2xl">
                                         
                  <h2 class="text-xl font-semibold mb-4">Zoho Mail Integration</h2>
                                         
                  <div id="zoho-status">
                                                
                     <p class="text-text-secondary mb-4">Connect your Zoho Mail account to send outreach emails directly from this panel.</p>
                                                 <button id="connect-zoho-btn" class="btn-primary px-5 py-3">
                                                     Connect to Zoho
                                                 </button>
                                            
                  </div>
                                    
               </div>
                            
            </div>
                    
         </main>
             
      </div>
              
      <div id="toast"></div>
          <script>
                 document.addEventListener('DOMContentLoaded', () => {
                     const authOverlay = document.getElementById('auth-overlay');
                     const loginForm = document.getElementById('login-form');
                     const mainContent = document.getElementById('main-content');
                     const authError = document.getElementById('auth-error');
                     const authModal = document.getElementById('auth-modal');
         
                     function initializeApp() {
                         mainContent.style.display = 'block';
                         setTimeout(() => { mainContent.style.opacity = '1'; }, 10);
         
                         // --- THEME & BG ---
                         const themeToggleBtn = document.getElementById('theme-toggle-btn');
                         const sunIconClass = 'fa-sun';
                         const moonIconClass = 'fa-moon';
         
                         const applyTheme = (theme) => {
                             const icon = themeToggleBtn.querySelector('i');
                             if (theme === 'dark') {
                                 document.documentElement.classList.add('dark');
                                 icon.classList.remove(moonIconClass);
                                 icon.classList.add(sunIconClass);
                             } else {
                                 document.documentElement.classList.remove('dark');
                                 icon.classList.remove(sunIconClass);
                                 icon.classList.add(moonIconClass);
                             }
                         };
                         const toggleTheme = () => {
                             const currentTheme = localStorage.getItem('theme') || 'dark';
                             const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                             localStorage.setItem('theme', newTheme);
                             applyTheme(newTheme);
                         };
                         themeToggleBtn.addEventListener('click', toggleTheme);
                         applyTheme(localStorage.getItem('theme') || 'dark');
         
                         const bodyElement = document.body;
                         if (bodyElement) {
                             const patternContainer = document.createElement('div');
                             patternContainer.className = 'pattern-container';
                             bodyElement.prepend(patternContainer);
                             
                             const iconCount = 50;
                             const minDistance = 18;
                             const placedIcons = [];
                             const maxAttempts = 100;
         
                             for (let i = 0; i < iconCount; i++) {
                                 let validPosition = false;
                                 let newIconPos = {};
                                 let attempts = 0;
         
                                 while (!validPosition && attempts < maxAttempts) {
                                     newIconPos = {
                                         top: Math.random() * 100,
                                         left: Math.random() * 100
                                     };
                                     let isOverlapping = false;
                                     for (const placedIcon of placedIcons) {
                                         const distTop = newIconPos.top - placedIcon.top;
                                         const distLeft = newIconPos.left - placedIcon.left;
                                         if (Math.sqrt(distTop * distTop + distLeft * distLeft) < minDistance) {
                                             isOverlapping = true;
                                             break;
                                         }
                                     }
                                     if (!isOverlapping) {
                                         validPosition = true;
                                     }
                                     attempts++;
                                 }
         
                                 if (validPosition) {
                                     placedIcons.push(newIconPos);
                                     const icon = document.createElement('span');
                                     icon.className = 'pattern-icon';
                                     const rotation = Math.random() * 360;
                                     const scale = 0.6 + Math.random() * 0.5;
                                     icon.style.top = `${newIconPos.top}%`;
                                     icon.style.left = `${newIconPos.left}%`;
                                     icon.style.transform = `translate(-50%, -50%) rotate(${rotation}deg) scale(${scale})`;
                                     icon.style.opacity = (0.03 + Math.random() * 0.04).toFixed(3);
                                     patternContainer.appendChild(icon);
                                 }
                             }
                         }
         
                         // --- APP LOGIC ---
                         let currentGeneratedEmail = null;
                         let isZohoConnected = <?php echo json_encode(isset($_SESSION['zoho_auth_status']) && $_SESSION['zoho_auth_status'] === 'connected'); ?>;
                         
                         const navTabs = document.getElementById('nav-tabs');
                         const pages = document.querySelectorAll('.page');
                         navTabs.addEventListener('click', (e) => {
                             if(e.target.tagName === 'BUTTON') {
                                 const pageId = e.target.getAttribute('data-page');
                                 
                                 navTabs.querySelectorAll('.nav-tab').forEach(tab => tab.classList.remove('active'));
                                 e.target.classList.add('active');
         
                                 pages.forEach(page => page.classList.remove('active'));
                                 document.getElementById(`${pageId}-page`).classList.add('active');
                             }
                         });
                         
                         const toast = document.getElementById('toast');
                         function showToast(message, type = 'success') {
                             toast.textContent = message;
                             toast.style.backgroundColor = type === 'error' ? '#ef4444' : 'var(--primary)';
                             toast.classList.add('show');
                             setTimeout(() => { toast.classList.remove('show'); }, 3000);
                         }
                         
                         window.copyToClipboard = function(elementId) {
                             const textarea = document.getElementById(elementId);
                             textarea.select();
                             navigator.clipboard.writeText(textarea.value).then(() => {
                                showToast('Copied to clipboard!');
                             }).catch(err => {
                                showToast('Failed to copy.', 'error');
                             });
                             window.getSelection().removeAllRanges();
                         }
                         
                         const draftForm = document.getElementById('draft-form');
                         const generateBtn = document.getElementById('generate-btn');
                         
                         draftForm.addEventListener('submit', (e) => {
                             e.preventDefault();
                             generateEmailWithAi();
                         });
                         
                        async function generateEmailWithAi() {
                            const businessName = document.getElementById('business-name').value;
                            const websiteStatus = document.getElementById('website-status').value;
                            const reason = document.getElementById('reason').value;

                            if (!businessName) {
                                showToast("Please enter a business name.", "error");
                                return;
                            }

                            const btnText = generateBtn.querySelector('.btn-text');
                            const loader = generateBtn.querySelector('.loader');
                            btnText.classList.add('hidden');
                            loader.classList.remove('hidden');
                            generateBtn.disabled = true;

                            try {
                                const response = await fetch('generate_email.php', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ businessName, websiteStatus, reason })
                                });

                                if (!response.ok) {
                                    const errorResult = await response.json();
                                    throw new Error(errorResult.error || `HTTP error! status: ${response.status}`);
                                }

                                const emailData = await response.json();

                                document.getElementById('output-subject').value = emailData.subject;
                                document.getElementById('output-body').value = `${emailData.body}\n\n${emailData.close_off}`;
                                document.getElementById('output-container').classList.remove('hidden');
                                document.getElementById('placeholder-output').classList.add('hidden');
                                document.getElementById('save-outreach-btn').disabled = false;
                                document.getElementById('send-outreach-btn').disabled = !isZohoConnected;
                                currentGeneratedEmail = emailData;

                            } catch (error) {
                                console.error("AI Generation Error:", error);
                                showToast(error.message || "Failed to generate email content.", "error");
                            } finally {
                                btnText.classList.remove('hidden');
                                loader.classList.add('hidden');
                                generateBtn.disabled = false;
                            }
                        }

                         const sendBtn = document.getElementById('send-outreach-btn');
                         const saveBtn = document.getElementById('save-outreach-btn');
         
                         sendBtn.addEventListener('click', () => sendEmailWithZoho());
                         saveBtn.addEventListener('click', () => logOutreach('manual'));
                         
                         async function sendEmailWithZoho() {
                            if (!currentGeneratedEmail) return;

                            const btnText = sendBtn.querySelector('.btn-text');
                            const loader = sendBtn.querySelector('.loader');
                            btnText.classList.add('hidden');
                            loader.classList.remove('hidden');
                            sendBtn.disabled = true;

                            try {
                                const response = await fetch('send-email.php', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({
                                        recipient_email: document.getElementById('recipient-email').value,
                                        subject: document.getElementById('output-subject').value,
                                        body: document.getElementById('output-body').value
                                    })
                                });

                                const result = await response.json();

                                if (result.success) {
                                    showToast(result.message, 'success');
                                    logOutreach('sent'); // Log it as sent
                                } else {
                                    showToast(result.message || 'Failed to send email.', 'error');
                                }

                            } catch (error) {
                                showToast('An error occurred while sending the email.', 'error');
                                console.error("Error sending email:", error);
                            } finally {
                                btnText.classList.remove('hidden');
                                loader.classList.add('hidden');
                                sendBtn.disabled = false;
                            }
                        }
         
                         function logOutreach(status) {
                             if (!currentGeneratedEmail) return;
                             
                             const logEntry = {
                                 ...currentGeneratedEmail,
                                 recipient_email: document.getElementById('recipient-email').value,
                                 subject: document.getElementById('output-subject').value,
                                 body: document.getElementById('output-body').value, 
                                 date: new Date().toISOString().split('T')[0],
                                 status: status
                             };
         
                             let log = JSON.parse(localStorage.getItem('outreachLog') || '[]');
                             log.unshift(logEntry);
                             localStorage.setItem('outreachLog', JSON.stringify(log));
         
                             document.getElementById('log-table-container').classList.remove('hidden');
                             document.getElementById('empty-log-placeholder').classList.add('hidden');
         
                             addLogRow(logEntry);
                             showToast(`Outreach logged as ${status}.`);
                             resetDraftingPanel();
                         }
         
                         function resetDraftingPanel() {
                             draftForm.reset();
                             document.getElementById('output-container').classList.add('hidden');
                             document.getElementById('placeholder-output').classList.remove('hidden');
                             document.getElementById('save-outreach-btn').disabled = true;
                             document.getElementById('send-outreach-btn').disabled = true;
                             currentGeneratedEmail = null;
                         }
         
                         function loadOutreachLog() {
                             let log = JSON.parse(localStorage.getItem('outreachLog') || '[]');
                             const tableContainer = document.getElementById('log-table-container');
                             const emptyPlaceholder = document.getElementById('empty-log-placeholder');
                             const tableBody = document.getElementById('log-table-body');
                             
                             tableBody.innerHTML = '';
         
                             if (log.length === 0) {
                                 tableContainer.classList.add('hidden');
                                 emptyPlaceholder.classList.remove('hidden');
                             } else {
                                 tableContainer.classList.remove('hidden');
                                 emptyPlaceholder.classList.add('hidden');
                                 log.forEach(item => addLogRow(item));
                             }
                         }
                         
                         // Function to escape HTML to prevent XSS
                         function escapeHTML(str) {
                            const p = document.createElement('p');
                            p.textContent = str;
                            return p.innerHTML;
                         }

                         function addLogRow(item) {
                             const tableBody = document.getElementById('log-table-body');
                             const newRow = document.createElement('tr');
                             const statusClass = item.status === 'sent' ? 'bg-green-500/20 text-green-400' : 'bg-yellow-500/20 text-yellow-400';
                             newRow.innerHTML = `
                                 <td class="px-6 py-4">${escapeHTML(item.business)}</td>
                                 <td class="px-6 py-4">${escapeHTML(item.subject)}</td>
                                 <td class="px-6 py-4 text-text-secondary">${escapeHTML(item.date)}</td>
                                 <td class="px-6 py-4"><span class="${statusClass} text-xs font-medium px-2.5 py-1 rounded-full capitalize">${escapeHTML(item.status)}</span></td>
                             `;
                             tableBody.appendChild(newRow);
                         }
         
                         function checkZohoStatus() {
                              const statusContainer = document.getElementById('zoho-status');
                              if (isZohoConnected) {
                                  statusContainer.innerHTML = `<p class="text-green-400 mb-4 flex items-center"><i class="fas fa-check-circle mr-2"></i>Zoho Mail is connected.</p><a href="disconnect_zoho.php" id="disconnect-zoho-btn" class="btn-secondary px-5 py-3 text-sm">Disconnect</a>`;
                              } else {
                                  statusContainer.innerHTML = `<p class="text-text-secondary mb-4">Connect your Zoho Mail account to send outreach emails directly from this panel.</p><a href="zoho-oauth.php" id="connect-zoho-btn" class="btn-primary px-5 py-3 text-center inline-block">Connect to Zoho</a>`;
                              }
                         }
                         
                         checkZohoStatus();
                         loadOutreachLog();
                     }
         
                     async function handleLogin(event) {
                        event.preventDefault();
                        const usernameInput = document.getElementById('username');
                        const passcodeİnput = document.getElementById('passcode');
                        const username = usernameInput.value;
                        const passcode = passcodeİnput.value;
                        authError.classList.add('hidden');

                        try {
                            const response = await fetch('../auth.php', { // Note the path is now ../auth.php
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ username, passcode })
                            });
                            const result = await response.json();

                            if (result.success) {
                                authOverlay.style.transition = 'opacity 0.5s ease-out';
                                authOverlay.style.opacity = '0';
                                setTimeout(() => {
                                    authOverlay.style.display = 'none';
                                    initializeApp();
                                }, 500);
                            } else {
                                authError.textContent = result.message || 'Invalid credentials. Please try again.';
                                authError.classList.remove('hidden');
                                authModal.classList.add('shake');
                                usernameInput.value = '';
                                passcodeİnput.value = '';
                                setTimeout(() => { authModal.classList.remove('shake'); }, 820);
                            }
                        } catch (error) {
                            console.error('Login request failed:', error);
                            authError.textContent = 'An error occurred. Please try again later.';
                            authError.classList.remove('hidden');
                        }
                    }

                    // Authentication Check from PHP session
                    const isAuthenticated = <?php echo json_encode(isset($_SESSION['isAuthenticated']) && $_SESSION['isAuthenticated']); ?>;

                    if (isAuthenticated) {
                         authOverlay.style.display = 'none';
                         initializeApp();
                    } else {
                         authOverlay.style.display = 'flex';
                         loginForm.addEventListener('submit', handleLogin);
                    }
                 });
             
      </script>
   </body>
</html>
