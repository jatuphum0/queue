<?php
session_start();

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['department'])) {
    header('Location: index.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบเจ้าหน้าที่ประจำแผนก</title>
    <style>
        @font-face {
        font-family: 'Sarabun';
        src: url('../fonts/Sarabun-Regular.ttf') format('truetype'),
            url('../fonts/Sarabun-Regular.ttf') format('truetype');
        font-weight: normal;
        font-style: normal;
        }         
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Sarabun', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 10px;
            margin-bottom: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .header h1 {
            color: #333;
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .current-time {
            font-size: 1.5em;
            color: #666;
            font-weight: 300;
            margin-bottom: 10px;
        }

        .department-info {
            font-size: 1.2em;
            color: #4CAF50;
            font-weight: bold;
            margin: 15px 0;
        }

        .logout-btn {
            background: linear-gradient(45deg, #FF5722, #D84315);
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            font-size: 0.9em;
            margin-top: 10px;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            background: linear-gradient(45deg, #D84315, #BF360C);
        }

        .doctor-selector-section {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            padding: 15px;
            margin: 15px 0;
            text-align: left;
        }

        .doctor-selector-section label {
            display: block;
            font-size: 1.1em;
            color: #333;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .doctor-selector {
            width: 100%;
            max-width: 400px;
            padding: 12px 15px;
            font-size: 1em;
            border: 2px solid #ddd;
            border-radius: 8px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .doctor-selector:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 8px rgba(76, 175, 80, 0.3);
        }

        .doctor-selector:hover {
            border-color: #4CAF50;
        }

        .doctor-info {
            background: rgba(33, 150, 243, 0.1);
            border-left: 4px solid #2196F3;
            padding: 10px 15px;
            margin: 10px 0;
            border-radius: 5px;
        }

        .doctor-name {
            font-weight: bold;
            color: #1976D2;
            font-size: 1.1em;
        }

        .main-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .section {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .section-header {
            padding: 20px;
            color: white;
            text-align: center;
            font-size: 1.5em;
            font-weight: bold;
        }

        .waiting-section .section-header {
            background: linear-gradient(45deg, #4CAF50, #45a049);
        }

        .called-section .section-header {
            background: linear-gradient(45deg, #2196F3, #1976D2);
        }

        .queue-list {
            padding: 20px;
            max-height: 600px;
            overflow-y: auto;
        }

        .queue-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 5px solid #4CAF50;
            transition: all 0.3s ease;
        }

        .called-item {
            border-left-color: #2196F3;
        }

        .queue-item:hover {
            background: rgba(76, 175, 80, 0.1);
            transform: translateX(3px);
        }

        .queue-number {
            background: linear-gradient(45deg, #FF9800, #F57C00);
            color: white;
            padding: 10px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 1.2em;
            min-width: 60px;
            text-align: center;
        }

        .patient-info {
            flex: 1;
            margin: 0 15px;
        }

        .patient-name {
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .queue-time {
            color: #666;
            font-size: 0.9em;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            font-size: 0.9em;
        }

        .btn-call {
            background: linear-gradient(45deg, #4CAF50, #45a049);
            color: white;
        }
        .btn-call:hover:not(:disabled) {
            background: linear-gradient(45deg, #45a049, #3d8b40);
            transform: translateY(-2px);
        }

        .btn-complete {
            background: linear-gradient(45deg, #2196F3, #1976D2);
            color: white;
        }
        .btn-complete:hover:not(:disabled) {
            background: linear-gradient(45deg, #1976D2, #1565C0);
            transform: translateY(-2px);
        }

        .btn-remove {
            background: linear-gradient(45deg, #f44336, #d32f2f);
            color: white;
        }
        .btn-remove:hover:not(:disabled) {
            background: linear-gradient(45deg, #d32f2f, #b71c1c);
            transform: translateY(-2px);
        }

        .btn:disabled {
            background: #ccc !important;
            cursor: not-allowed !important;
            transform: none !important;
            opacity: 0.6;
        }

        .btn-calling {
            background: linear-gradient(45deg, #FF5722, #D84315) !important;
            animation: calling-pulse 1s infinite;
        }

        @keyframes calling-pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }

        .no-queue {
            text-align: center;
            color: #999;
            padding: 40px;
            font-style: italic;
            font-size: 1.1em;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 2s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
            }
            
            .header h1 {
                font-size: 2em;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php echo htmlspecialchars($_SESSION['department_name']); ?></h1>
            <div class="current-time" id="currentTime"></div>
            <!-- <div class="doctor-selector-section">
                <div id="selectedDoctorInfo" class="doctor-info" style="display: none;">
                    <div class="doctor-name" id="selectedDoctorName"></div>
                </div>
            </div> -->
            
            <div class="search-section" style="margin: 20px 0;">
                <div style="display: flex; gap: 10px; justify-content: center; align-items: center; flex-wrap: wrap;">
                    <input type="text" id="searchInput" placeholder="ค้นหาชื่อผู้ป่วย หรือ HN" 
                           style="padding: 10px; border: 2px solid #ddd; border-radius: 25px; font-size: 1em; width: 300px; text-align: center;">
                    <button onclick="searchPatient()" style="padding: 10px 20px; background: linear-gradient(45deg, #2196F3, #1976D2); color: white; border: none; border-radius: 25px; cursor: pointer; font-weight: bold;">ค้นหา</button>
                    <button onclick="clearSearch()" style="padding: 10px 20px; background: linear-gradient(45deg, #FF9800, #F57C00); color: white; border: none; border-radius: 25px; cursor: pointer; font-weight: bold;">ล้างการค้นหา</button>
                    <button style="padding: 10px 20px; background: linear-gradient(45deg,rgb(255, 0, 0),rgb(233, 119, 5)); color: white; border: none; border-radius: 25px; cursor: pointer; font-weight: bold;" onclick="window.location.href='logout.php'">ออกจากระบบ</button>
                </div>
                <div id="searchResult" style="margin-top: 15px; text-align: center;"></div> 
            </div>
            
        </div>
        
        <div class="main-content">
            <!-- รายการคิวรอ -->
            <div class="section waiting-section">
                <div class="section-header">
                    <div>รายการคิวรอ <span id="waitingCount">จำนวน: 0 รายการ</span></div>
                    <select id="doctorFilter" class="doctor-selector">
                        <option value="">-- แสดงคิวทั้งหมด --</option>
                    </select>
                    <!-- <div id="waitingCount">จำนวน: 0 รายการ</div> -->
                    <div id="totalQueueCount" style="margin-top: 10px; font-size: 0.9em; color: #ffffff;">จำนวนคิวทั้งหมดของแผนก: 0</div>
                    <!-- <div style="margin-top: 5px; font-size: 0.8em; color: #ffffff; opacity: 0.8;">แสดง 20 รายการล่าสุด</div> -->
                </div>
                <div class="queue-list" id="waitingList">
                    <div class="loading">
                        <div class="spinner"></div>
                        กำลังโหลดข้อมูล...
                    </div>
                </div>
            </div>

            <!-- รายการคิวที่เรียกแล้ว -->
            <div class="section called-section">
                <div class="section-header">
                    <div>รายการคิวที่เรียกแล้ว</div>
                    <div id="calledCount">จำนวน: 0 รายการ</div>
                    <div style="margin-top: 5px; font-size: 0.8em; color: #ffffff; opacity: 0.8;">แสดง 20 รายการล่าสุด</div>
                </div>
                <div class="queue-list" id="calledList">
                    <div class="loading">
                        <div class="spinner"></div>
                        กำลังโหลดข้อมูล...
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        class StaffSystem {
            constructor() {
                this.updateInterval = 5000;
                this.selectedDepartment = '<?php echo $_SESSION["department"]; ?>';
                this.departmentName = '<?php echo $_SESSION["department_name"]; ?>';
                this.speechSynthesis = window.speechSynthesis;
                this.currentlyCalling = new Set();
                this.selectedDoctor = '';
                this.allWaitingQueues = [];
                this.availableDoctors = {};
                this.init();
            }

            init() {
                this.updateCurrentTime();
                this.setupEventListeners();
                this.loadAllData();
                
                setInterval(() => this.updateCurrentTime(), 1000);
                setInterval(() => this.loadAllData(), this.updateInterval);
            }

            setupEventListeners() {
                document.getElementById('doctorFilter').addEventListener('change', (e) => {
                    this.selectedDoctor = e.target.value;
                    // this.updateSelectedDoctorInfo();
                    this.filterAndRenderQueues();
                });
            }

            updateCurrentTime() {
                const now = new Date();
                const options = {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                };
                
                document.getElementById('currentTime').textContent = 
                    now.toLocaleDateString('th-TH', options);
            }

            async loadAllData() {
                await Promise.all([
                    this.loadWaitingQueue(),
                    this.loadCalledQueue(),
                    this.loadQueueCounts()
                ]);
            }

            async loadWaitingQueue() {
                try {
                    const response = await fetch(`../get_queue_data.php?department=${encodeURIComponent(this.selectedDepartment)}`);
                    const data = await response.json();
                    if (data.success) {
                        this.allWaitingQueues = data.queues;
                        this.extractDoctorInfo(data.queues);
                        this.populateDoctorDropdown();
                        this.filterAndRenderQueues();
                    }
                } catch (error) {
                    console.error('Error loading waiting queue:', error);
                }
            }

            async loadCalledQueue() {
                try {
                    const response = await fetch(`../get_called_queue.php?department=${encodeURIComponent(this.selectedDepartment)}`);
                    const data = await response.json();
                    
                    if (data.success) {
                        this.renderCalledQueue(data.queues);
                        this.updateCalledCount(data.queues.length);
                    }
                } catch (error) {
                    console.error('Error loading called queue:', error);
                }
            }

            extractDoctorInfo(queues) {
                this.availableDoctors = {};
                queues.forEach(queue => {
                    const doctorKey = queue.doctor || 'ไม่ระบุหมอ';
                    const doctorName = queue.doctor_name || 'ไม่ระบุชื่อหมอ';
                    
                    if (!this.availableDoctors[doctorKey]) {
                        this.availableDoctors[doctorKey] = {
                            name: doctorName,
                            queues: []
                        };
                    }
                });
            }

            populateDoctorDropdown() {
                const select = document.getElementById('doctorFilter');
                const currentValue = select.value;
                
                // Clear existing options (except first one)
                while (select.children.length > 1) {
                    select.removeChild(select.lastChild);
                }
                
                // Add doctor options
                Object.entries(this.availableDoctors).forEach(([doctorKey, doctorInfo]) => {
                    const option = document.createElement('option');
                    option.value = doctorKey;
                    option.textContent = doctorInfo.name;
                    select.appendChild(option);
                });
                
                // Restore previous selection if still valid
                if (currentValue && Object.keys(this.availableDoctors).includes(currentValue)) {
                    select.value = currentValue;
                    this.selectedDoctor = currentValue;
                }
            }

            // updateSelectedDoctorInfo() {
            //     const infoDiv = document.getElementById('selectedDoctorInfo');
            //     const nameDiv = document.getElementById('selectedDoctorName');
                
            //     if (this.selectedDoctor && this.availableDoctors[this.selectedDoctor]) {
            //         nameDiv.textContent = `แสดงคิวของ: ${this.availableDoctors[this.selectedDoctor].name}`;
            //         infoDiv.style.display = 'block';
            //     } else {
            //         infoDiv.style.display = 'none';
            //     }
            // }

            filterAndRenderQueues() {
                let filteredQueues = this.allWaitingQueues;
                
                if (this.selectedDoctor) {
                    filteredQueues = this.allWaitingQueues.filter(queue => 
                        (queue.doctor || 'ไม่ระบุหมอ') === this.selectedDoctor
                    );
                }
                
                this.renderWaitingQueue(filteredQueues);
                this.updateWaitingCount(filteredQueues.length);
            }

            renderWaitingQueue(queues) {
                const waitingList = document.getElementById('waitingList');
                
                if (queues.length === 0) {
                    const message = this.selectedDoctor ? 
                        `ไม่มีคิวรอสำหรับ ${this.availableDoctors[this.selectedDoctor]?.name || 'หมอที่เลือก'}` : 
                        'ไม่มีคิวรอในขณะนี้';
                    waitingList.innerHTML = `<div class="no-queue">${message}</div>`;
                    return;
                }

                const html = queues.map((queue, index) => this.renderWaitingItem(queue, index)).join('');
                waitingList.innerHTML = html;
            }

            renderCalledQueue(queues) {
                const calledList = document.getElementById('calledList');
                
                if (queues.length === 0) {
                    calledList.innerHTML = '<div class="no-queue">ยังไม่มีการเรียกคิว</div>';
                    return;
                }

                const html = queues.map(queue => this.renderCalledItem(queue)).join('');
                calledList.innerHTML = html;
            }

            renderWaitingItem(queue, index) {
                const firstName = queue.name;
                const isCalling = this.currentlyCalling.has(queue.oqueue);
                const callButtonClass = isCalling ? 'btn-calling' : '';
                const callButtonText = isCalling ? '🔊 กำลังเรียก...' : '🔊 เรียกชื่อ';
                const callButtonDisabled = isCalling ? 'disabled' : '';
                const doctorInfo = queue.doctor_name ? `<strong>หมอ:</strong> ${queue.doctor_name}` : '';
                
                return `
                    <div class="queue-item">
                        <div class="queue-number">${index + 1}</div>
                        <div class="patient-info">
                            <div class="patient-name">${firstName}</div>
                            <div class="queue-time">
                                <strong>เวลาลงทะเบียน:</strong> ${queue.cur_dep_time} 
                                <strong>HN:</strong> <span class="patient-hn">${queue.hn}</span>
                                ${doctorInfo ? `<br>${doctorInfo}` : ''}
                            </div>
                        </div>
                        <div class="action-buttons">
                            <button class="btn btn-call ${callButtonClass}" 
                                    onclick="staffSystem.callPatient('${queue.name}', ${queue.oqueue})"
                                    ${callButtonDisabled}>
                                ${callButtonText}
                            </button>
                            <button class="btn btn-complete" onclick="staffSystem.completeQueue('${queue.vn}', '${queue.hn}', '${queue.name}', ${queue.oqueue})">
                                ✅ เรียกแล้ว
                            </button>
                        </div>
                    </div>
                `;
            }

            renderCalledItem(queue) {
                const firstName = queue.name;
                return `
                    <div class="queue-item called-item">
                        <div class="patient-info">
                            <div class="patient-name">${firstName}</div>
                            <div class="queue-time">
                                เรียกเมื่อ: ${new Date(queue.called_time).toLocaleString('th-TH')}
                                <br><strong>HN:</strong> ${queue.hn}
                            </div>
                        </div>
                        <div class="action-buttons">
                            <button class="btn btn-remove" onclick="staffSystem.removeCalledQueue('${queue.vn}', '${queue.hn}', '${queue.name}')" 
                                    style="background: linear-gradient(45deg, #f44336, #d32f2f); color: white;">
                                🗑️ ลบ
                            </button>
                        </div>
                    </div>
                `;
            }

            updateWaitingCount(count) {
                document.getElementById('waitingCount').textContent = `จำนวน: ${count} รายการ`;
            }

            updateCalledCount(count) {
                document.getElementById('calledCount').textContent = `จำนวน: ${count} รายการ`;
            }

            async loadQueueCounts() {
                try {
                    const response = await fetch(`../get_queue_count.php?department=${encodeURIComponent(this.selectedDepartment)}`);
                    const data = await response.json();
                    if (data.success) {
                        this.updateTotalQueueCount(data.counts.total);
                    }
                } catch (error) {
                    console.error('Error loading queue counts:', error);
                }
            }

            updateTotalQueueCount(count) {
                document.getElementById('totalQueueCount').textContent = `จำนวนคิวทั้งหมดของแผนก: ${count}`;
            }

            callPatient(patientName, queueNumber) {
                if (this.currentlyCalling.has(queueNumber)) {
                    return;
                }
                this.currentlyCalling.add(queueNumber);
                this.updateCallButton(queueNumber, true);

                this.speechSynthesis.cancel();                

                const text = `เชิญ ${patientName} ที่${this.departmentName} ค่ะ`; //หมายเลข ${queueNumber}
                
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = 'th-TH';
                utterance.rate = 0.65; // ความเร็วเสียงพูด
                utterance.pitch = 1.0;
                
                const voices = this.speechSynthesis.getVoices();
                let thaiFemaleVoice = null;

                for (let i = 0; i < voices.length; i++) {
                    if (voices[i].lang === 'th-TH') {
                        if (voices[i].name.toLowerCase().includes('female') || 
                            voices[i].name.includes('Premwadee') || 
                            voices[i].name.includes('Narisa') || 
                            voices[i].name.includes('Kanya')) {
                            thaiFemaleVoice = voices[i];
                            break;
                        }
                        if (!thaiFemaleVoice) {
                            thaiFemaleVoice = voices[i];
                        }
                    }
                }
                
                if (thaiFemaleVoice) {
                    utterance.voice = thaiFemaleVoice;
                }

                utterance.onend = () => {
                    this.currentlyCalling.delete(queueNumber);
                    this.updateCallButton(queueNumber, false);
                    console.log(`เรียกชื่อคิว ${queueNumber} เสร็จแล้ว`);
                };

                utterance.onerror = () => {
                    this.currentlyCalling.delete(queueNumber);
                    this.updateCallButton(queueNumber, false);
                    console.error(`เกิดข้อผิดพลาดในการเรียกคิว ${queueNumber}`);
                };

                this.speechSynthesis.speak(utterance);
            }

            updateCallButton(queueNumber, isCalling) {
                const button = document.querySelector(`button[onclick*="${queueNumber}"]`);

                if (button) {
                    if (isCalling) {
                        button.classList.add('btn-calling');
                        button.disabled = true;
                        button.textContent = '🔊 กำลังเรียก...';
                    } else {
                        button.classList.remove('btn-calling');
                        button.disabled = false;
                        button.textContent = '🔊 เรียกชื่อ';
                    }
                }
            }

            async completeQueue(vn, hn, name, queueNumber) {
                try {
                    const response = await fetch('../call_queue.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `vn=${encodeURIComponent(vn)}&hn=${encodeURIComponent(hn)}&name=${encodeURIComponent(name)}&oqueue=${encodeURIComponent(queueNumber)}&cur_dep=${encodeURIComponent(this.selectedDepartment)}`
                    });

                    const data = await response.json();
                    
                    if (data.success) {
                        this.loadAllData();
                    } else {
                        console.error('Error completing queue:', data.message);
                    }
                } catch (error) {
                    console.error('Error completing queue:', error);
                }
            }

            async removeCalledQueue(vn, hn, name) {
                if (!confirm(`ต้องการลบ ${name} (HN: ${hn}) ออกจากรายการที่เรียกแล้วหรือไม่?`)) {
                    return;
                }

                try {
                    const response = await fetch('remove_called_queue.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `vn=${encodeURIComponent(vn)}&hn=${encodeURIComponent(hn)}&cur_dep=${encodeURIComponent(this.selectedDepartment)}`
                    });

                    const data = await response.json();
                    
                    if (data.success) {
                        alert('ลบรายการเรียบร้อยแล้ว');
                        this.loadAllData();
                    } else {
                        alert('เกิดข้อผิดพลาด: ' + (data.message || data.error));
                    }
                } catch (error) {
                    console.error('Error removing called queue:', error);
                    alert('เกิดข้อผิดพลาดในการลบรายการ');
                }
            }
        }

        const staffSystem = new StaffSystem();

        async function searchPatient() {
            const searchTerm = document.getElementById('searchInput').value.trim();
            if (!searchTerm) {
                alert('กรุณาใส่ชื่อผู้ป่วยหรือ HN ที่ต้องการค้นหา');
                return;
            }

            const resultDiv = document.getElementById('searchResult');
            resultDiv.innerHTML = '<div style="color: #666;">กำลังค้นหา...</div>';

            try {
                const response = await fetch('search_queue.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `department=${encodeURIComponent(staffSystem.selectedDepartment)}&search=${encodeURIComponent(searchTerm)}`
                });

                const data = await response.json();
                
                if (data.success) {
                    displaySearchResults(data.results, searchTerm);
                } else {
                    resultDiv.innerHTML = `<div style="color: #ff4444; font-weight: bold;">เกิดข้อผิดพลาด: ${data.error}</div>`;
                }
            } catch (error) {
                console.error('Search error:', error);
                resultDiv.innerHTML = '<div style="color: #ff4444; font-weight: bold;">เกิดข้อผิดพลาดในการค้นหา</div>';
            }
        }

        function displaySearchResults(results, searchTerm) {
            const resultDiv = document.getElementById('searchResult');
            
            if (results.length === 0) {
                resultDiv.innerHTML = `<div style="color: #ff4444; font-weight: bold;">ไม่พบผู้ป่วยชื่อ "${searchTerm}"</div>`;
                return;
            }
            
            let html = `<div style="color: #2196F3; font-weight: bold; margin-bottom: 10px;">ผลการค้นหา "${searchTerm}" (${results.length} รายการ):</div>`;
            
            results.forEach(result => {
                const statusColor = result.status === 'waiting' ? '#4CAF50' : '#FF9800';
                const statusText = result.status === 'waiting' ? 'รอ' : 'เรียกแล้ว';
                const doctorInfo = result.doctor_name ? `<div style="font-size: 0.8em; color: #666;">หมอ: ${result.doctor_name}</div>` : '';
                
                let positionInfo = '';
                if (result.status === 'waiting') {
                    if (result.doctor_name) {
                        positionInfo = `ลำดับในแผนก: ${result.position} | ลำดับของหมอ: ${result.doctor_position}`;
                    } else {
                        positionInfo = `ลำดับในแผนก: ${result.position}`;
                    }
                } else {
                    positionInfo = 'เรียกแล้ว';
                }
                
                html += `
                    <div style="display: inline-block; margin: 5px; padding: 10px 15px; background: white; border: 2px solid ${statusColor}; border-radius: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                        <div style="font-weight: bold; color: #333;">${result.name}</div>
                        <div style="font-weight: bold; color: #333;">HN: ${result.hn}</div>
                        <div style="font-size: 0.9em; color: ${statusColor};">
                            ${positionInfo}
                        </div>
                        <div style="font-size: 0.8em; color: #666;">เวลา: ${result.time}</div>
                        ${doctorInfo}
                        <div style="font-size: 0.8em; color: #666;">สถานะ: ${statusText}</div>
                    </div>
                `;
            });
            
            resultDiv.innerHTML = html;
        }

        function clearSearch() {
            document.getElementById('searchInput').value = '';
            document.getElementById('searchResult').innerHTML = '';
        }

        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchPatient();
            }
        });
    </script>
</body>
</html>