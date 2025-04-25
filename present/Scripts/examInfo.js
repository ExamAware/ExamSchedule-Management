document.addEventListener("DOMContentLoaded", () => {
    const examNameElem = document.getElementById("examName");
    const messageElem = document.getElementById("message");
    const currentTimeElem = document.getElementById("current-time");
    const currentSubjectElem = document.getElementById("current-subject");
    const examTimingElem = document.getElementById("exam-timing");
    const remainingTimeElem = document.getElementById("remaining-time");
    const statusElem = document.getElementById("status");
    const examTableBodyElem = document.getElementById("exam-table-body");
    const roomElem = document.getElementById("room");
    const infoToggleBtn = document.getElementById("info-toggle-btn");
    const paperInfoElem = document.getElementById("paper-info");
    const examPapersElem = document.getElementById("exam-papers");
    const answerSheetsElem = document.getElementById("answer-sheets");
    let offsetTime = getCookie("offsetTime") || 0;
    let showPaperInfo = getCookie("showPaperInfo") === "true";
    let autoToggle = getCookie("autoToggle") === "true";

    // 初始化显示状态
    if (showPaperInfo) {
        currentSubjectElem.style.display = "none";
        paperInfoElem.style.display = "block";
    }

    // 更新显示内容
    function updateDisplay(isExamTime) {
        if (autoToggle) {
            // 在考试期间显示页数，其他时间显示表格
            paperInfoElem.style.display = isExamTime ? "block" : "none";
            currentSubjectElem.style.display = "block";
        }
    }

    infoToggleBtn.addEventListener("click", () => {
        if (!autoToggle) {
            showPaperInfo = !showPaperInfo;
            paperInfoElem.style.display = showPaperInfo ? "block" : "none";
            currentSubjectElem.style.display = "block";
            setCookie("showPaperInfo", showPaperInfo, 365);
        }
    });

    function fetchData() {
        const urlParams = new URLSearchParams(window.location.search);
        const configId = urlParams.get('configId');
        
        if (!configId) {
            errorSystem.show('未提供配置ID，请从主页进入');
            return;
        }

        fetch(`/api/get_config.php?id=${encodeURIComponent(configId)}`)
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw err; });
                }
                return response.json();
            })
            .then(data => {
                displayExamInfo(data);
                updateCurrentTime();
                updateExamInfo(data);
                setInterval(() => updateCurrentTime(), 1000);
                setInterval(() => updateExamInfo(data), 1000);
            })
            .catch(error => errorSystem.show('获取考试数据失败: ' + error.message));
    }

    function displayExamInfo(data) {
        try {
            const examNameText = data.examName;
            const roomText = data.room || roomElem.textContent;
            examNameElem.innerHTML = `${examNameText} <span id="room">${roomText}</span>`;
            messageElem.textContent = data.message;
        } catch (e) {
            errorSystem.show('显示考试信息失败: ' + e.message);
        }
    }

    function updateCurrentTime() {
        try {
            const now = new Date(new Date().getTime() + offsetTime * 1000);
            currentTimeElem.textContent = now.toLocaleTimeString('zh-CN', { hour12: false });
        } catch (e) {
            errorSystem.show('更新时间失败: ' + e.message);
        }
    }

    function updateExamInfo(data) {
        try {
            const now = new Date(new Date().getTime() + offsetTime * 1000);
            let currentExam = null;
            let nextExam = null;
            let lastExam = null;

            data.examInfos.forEach(exam => {
                const start = new Date(exam.start);
                const end = new Date(exam.end);
                if (now >= start && now <= end) {
                    currentExam = exam;
                }
                if (!currentExam && !nextExam && now < start) {
                    nextExam = exam;
                }
                if (now > end && (!lastExam || end > new Date(lastExam.end))) {
                    lastExam = exam;
                    isnotificated = false;
                }
            });

            // 清空原有内容
            if (paperInfoElem) paperInfoElem.style.display = showPaperInfo ? "block" : "none";
            if (currentSubjectElem) currentSubjectElem.style.display = showPaperInfo ? "none" : "block";

            if (currentExam) {
                const currentStatus = `当前科目: ${currentExam.name}`;
                if (!showPaperInfo) {
                    currentSubjectElem.textContent = currentStatus;
                }
                currentSubjectElem.style.display = "block"; // 总是显示科目信息
                paperInfoElem.style.display = showPaperInfo ? "block" : "none";

                // 加载本地存储的页数信息
                if (showPaperInfo) {
                    // 加载本地保存的页数信息
                    const paperCount = document.getElementById('paper-count');
                    const paperPages = document.getElementById('paper-pages');
                    const sheetCount = document.getElementById('sheet-count');
                    const sheetPages = document.getElementById('sheet-pages');
                    
                    if (paperCount && paperPages && sheetCount && sheetPages) {
                        try {
                            const savedInfo = localStorage.getItem('paperInfo');
                            if (savedInfo) {
                                const info = JSON.parse(savedInfo);
                                paperCount.value = info.paperCount || 0;
                                paperPages.value = info.paperPages || 0;
                                sheetCount.value = info.sheetCount || 0;
                                sheetPages.value = info.sheetPages || 0;
                            }
                        } catch (e) {
                            console.error('加载页数信息失败:', e);
                        }
                    }
                }

                if (examTimingElem) {
                    examTimingElem.textContent = `起止时间: ${formatTimeWithoutSeconds(new Date(currentExam.start).toLocaleTimeString('zh-CN', { hour12: false }))} - ${formatTimeWithoutSeconds(new Date(currentExam.end).toLocaleTimeString('zh-CN', { hour12: false }))}`;
                }

                const remainingTime = (new Date(currentExam.end).getTime() - now.getTime() + 1000) / 1000;
                const remainingHours = Math.floor(remainingTime / 3600);
                const remainingMinutes = Math.floor((remainingTime % 3600) / 60);
                const remainingSeconds = Math.floor(remainingTime % 60);
                const remainingTimeText = `${remainingHours}时 ${remainingMinutes}分 ${remainingSeconds}秒`;

                if (remainingHours === 0 && remainingMinutes <= 14) {
                    if (remainingTimeElem) {
                        remainingTimeElem.textContent = `倒计时: ${remainingTimeText}`;
                        remainingTimeElem.style.color = "red";
                        remainingTimeElem.style.fontWeight = "bold";
                    }
                    if (statusElem) {
                        statusElem.textContent = "状态: 即将结束";
                        statusElem.style.color = "red";
                    }

                    // 在剩余15分钟时显示提醒
                    if (isnotificated === false && remainingMinutes === 14 && remainingSeconds === 59) {
                        isnotificated = true;
                        const overlay = document.getElementById('reminder-overlay');
                        if (overlay) {
                            overlay.classList.add('show');
                            setTimeout(() => {
                                overlay.classList.remove('show');
                            }, 5000);
                        }
                    }
                } else {
                    if (remainingTimeElem) {
                        remainingTimeElem.textContent = `剩余时间: ${remainingTimeText}`;
                        remainingTimeElem.style.color = "#93b4f7";
                        remainingTimeElem.style.fontWeight = "normal";
                    }
                    if (statusElem) {
                        statusElem.textContent = "状态: 进行中";
                        statusElem.style.color = "#5ba838";
                    }
                }
            } else {
                // 非考试时间，显示表格
                updateDisplay(false);
                if (lastExam && now < new Date(lastExam.end).getTime() + 60000) {
                    if (currentSubjectElem) currentSubjectElem.textContent = `上场科目: ${lastExam.name}`;
                    if (examTimingElem) examTimingElem.textContent = "";
                    if (remainingTimeElem) remainingTimeElem.textContent = "";
                    if (statusElem) {
                        statusElem.textContent = "状态: 已结束";
                        statusElem.style.color = "red";
                    }
                } else if (nextExam) {
                    const timeUntilStart = ((new Date(nextExam.start).getTime() - now.getTime()) / 1000) + 1;
                    const remainingHours = Math.floor(timeUntilStart / 3600);
                    const remainingMinutes = Math.floor((timeUntilStart % 3600) / 60);
                    const remainingSeconds = Math.floor(timeUntilStart % 60);
                    const remainingTimeText = `${remainingHours}时 ${remainingMinutes}分 ${remainingSeconds}秒`;

                    if (timeUntilStart <= 15 * 60) {
                        currentSubjectElem.textContent = `即将开始: ${nextExam.name}`;
                        remainingTimeElem.textContent = `倒计时: ${remainingTimeText}`;
                        remainingTimeElem.style.color = "orange";
                        remainingTimeElem.style.fontWeight = "bold";
                        statusElem.textContent = "状态: 即将开始";
                        statusElem.style.color = "#DBA014";
                    } else {
                        currentSubjectElem.textContent = `下一场科目: ${nextExam.name}`;
                        remainingTimeElem.textContent = "";
                        statusElem.textContent = "状态: 未开始";
                        remainingTimeElem.style.fontWeight = "normal";
                        statusElem.style.color = "#EAEE5B";
                    }

                    examTimingElem.textContent = `起止时间: ${formatTimeWithoutSeconds(new Date(nextExam.start).toLocaleTimeString('zh-CN', { hour12: false }))} - ${formatTimeWithoutSeconds(new Date(nextExam.end).toLocaleTimeString('zh-CN', { hour12: false }))}`;
                } else {
                    if (currentSubjectElem) currentSubjectElem.textContent = "考试均已结束";
                    if (examTimingElem) examTimingElem.textContent = "";
                    if (remainingTimeElem) remainingTimeElem.textContent = "";
                    if (statusElem) {
                        statusElem.textContent = "状态: 空闲";
                        statusElem.style.color = "#3946AF";
                    }
                }
            }

            examTableBodyElem.innerHTML = "";
            
            // 预处理日期和时间段
            const dateGroups = {};
            data.examInfos.forEach(exam => {
                const start = new Date(exam.start);
                const hour = start.getHours();
                const dateStr = `${start.getMonth() + 1}月${start.getDate()}日<br>${hour < 12 ? '上午' : (hour < 18 ? '下午' : '晚上')}`;
                
                if (!dateGroups[dateStr]) {
                    dateGroups[dateStr] = [];
                }
                dateGroups[dateStr].push(exam);
            });

            // 生成表格
            Object.entries(dateGroups).forEach(([dateStr, exams]) => {
                let isFirstRow = true;
                // 计算实际需要的行数（考虑科目名称中的斜杠）
                const totalRows = exams.reduce((acc, exam) => {
                    return acc + (exam.name.includes('/') ? exam.name.split('/').length : 1);
                }, 0);

                exams.forEach(exam => {
                    const start = new Date(exam.start);
                    const end = new Date(exam.end);
                    const now = new Date(new Date().getTime() + offsetTime * 1000);

                    let status = "未开始";
                    if (now < start) {
                        status = now > new Date(start.getTime() - 15 * 60 * 1000) ? "即将开始" : "未开始";
                    } else if (now > end) {
                        status = "已结束";
                    } else {
                        status = "进行中";
                    }

                    // 处理包含斜杠的科目名称
                    const subjects = exam.name.split('/');
                    subjects.forEach((subject, index) => {
                        const row = document.createElement("tr");
                        let cells = '';

                        if (isFirstRow) {
                            cells = `<td rowspan="${totalRows}">${dateStr}</td>`;
                            isFirstRow = false;
                        }

                        // 仅在第一个科目行添加时间和状态
                        if (index === 0) {
                            cells += `
                                <td>${subject.trim()}</td>
                                <td rowspan="${subjects.length}">${formatTimeWithoutSeconds(start.toLocaleTimeString('zh-CN', { hour12: false }))}</td>
                                <td rowspan="${subjects.length}">${formatTimeWithoutSeconds(end.toLocaleTimeString('zh-CN', { hour12: false }))}</td>
                                <td rowspan="${subjects.length}"><span class="exam-status-tag exam-status-${status}">${status}</span></td>
                            `;
                        } else {
                            cells += `<td>${subject.trim()}</td>`;
                        }

                        row.innerHTML = cells;
                        examTableBodyElem.appendChild(row);
                    });
                });
            });
        } catch (e) {
            console.error('更新考试信息失败:', e);
            errorSystem.show('更新考试信息失败: ' + e.message);
        }
    }

    // 添加页数控制处理
    document.querySelectorAll('.count-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = document.getElementById(btn.dataset.target);
            const action = btn.dataset.action;
            const currentValue = parseInt(target.value) || 0;
            
            if (action === 'increase') {
                target.value = currentValue + 1;
            } else if (action === 'decrease' && currentValue > 0) {
                target.value = currentValue - 1;
            }
            
            // 保存到localStorage
            updatePaperInfo();
        });
    });

    // 监听输入框直接输入
    ['paper-count', 'paper-pages', 'sheet-count', 'sheet-pages'].forEach(id => {
        const input = document.getElementById(id);
        input.addEventListener('change', () => {
            const value = parseInt(input.value) || 0;
            input.value = Math.max(0, value); // 确保不小于0
            updatePaperInfo();
        });
    });

    function updatePaperInfo() {
        const paperInfo = {
            paperCount: parseInt(document.getElementById('paper-count').value) || 0,
            paperPages: parseInt(document.getElementById('paper-pages').value) || 0,
            sheetCount: parseInt(document.getElementById('sheet-count').value) || 0,
            sheetPages: parseInt(document.getElementById('sheet-pages').value) || 0
        };
        localStorage.setItem('paperInfo', JSON.stringify(paperInfo));
    }

    // 初始化加载保存的页数信息
    function loadPaperInfo() {
        try {
            const savedInfo = localStorage.getItem('paperInfo');
            if (savedInfo) {
                const info = JSON.parse(savedInfo);
                document.getElementById('paper-count').value = info.paperCount || 0;
                document.getElementById('paper-pages').value = info.paperPages || 0;
                document.getElementById('sheet-count').value = info.sheetCount || 0;
                document.getElementById('sheet-pages').value = info.sheetPages || 0;
            }
        } catch (e) {
            console.error('加载页数信息失败:', e);
        }
    }

    loadPaperInfo();

    fetchData();
});
