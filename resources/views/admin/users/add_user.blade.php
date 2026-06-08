<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('messages.add_new_member') }} - HRC System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 font-sans">

    <div class="min-h-screen flex flex-col items-center justify-center p-6">
        
        <div class="w-full max-w-2xl flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-slate-800">{{ __('messages.manage_new_member') }}</h1>
            <a href="{{ route('admin.users.index') }}" class="flex items-center text-slate-600 hover:text-blue-600 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                {{ __('messages.back_to_management') }}
            </a>
        </div>

        <div class="bg-white w-full max-w-2xl rounded-xl shadow-lg overflow-hidden">
            <div class="bg-blue-800 p-4">
                <p class="text-white text-center font-medium">{{ __('messages.fill_employee_info') }}</p>
            </div>

            @if($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 m-6 mb-0">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.users.store') }}" method="POST" class="p-8 grid grid-cols-2 gap-6">
                @csrf
                
                <div class="col-span-1">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('messages.first_name') }}</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="{{ __('messages.placeholder_first_name') }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                </div>

                <div class="col-span-1">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('messages.last_name') }}</label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" placeholder="{{ __('messages.placeholder_last_name') }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                </div>

                <div class="col-span-1">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('messages.username_login') }}</label>
                    <input type="text" name="username" value="{{ old('username') }}" placeholder="{{ __('messages.placeholder_username') }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                </div>

                <div class="col-span-1">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('messages.password') }}</label>
                    <input type="password" name="password" placeholder="{{ __('messages.placeholder_password') }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                </div>

                <div class="col-span-1">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('messages.branch') }}</label>
                    <div class="flex gap-1">
                        <select id="select-branch" name="branch" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white" required>
                            <option value="">-- {{ __('messages.select_branch') }} --</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->name }}" data-id="{{ $branch->id }}" {{ old('branch') == $branch->name ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        <button type="button" onclick="openModal('branch')" class="px-2.5 py-2 bg-blue-800 text-white font-bold rounded-lg hover:bg-blue-900 transition shadow" title="{{ __('messages.add_branch') }}">+</button>
                        <button type="button" onclick="openManageModal('branch')" class="px-2.5 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition shadow text-xs" title="{{ __('messages.manage_branch') }}">📝</button>
                    </div>
                </div>

                <div class="col-span-1">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('messages.department') }}</label>
                    <div class="flex gap-1">
                        <select id="select-department" name="department" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white" required>
                            <option value="">-- {{ __('messages.select_department') }} --</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->name }}" data-id="{{ $department->id }}" {{ old('department') == $department->name ? 'selected' : '' }}>{{ $department->name }}</option>
                            @endforeach
                        </select>
                        <button type="button" onclick="openModal('department')" class="px-2.5 py-2 bg-blue-800 text-white font-bold rounded-lg hover:bg-blue-900 transition shadow" title="{{ __('messages.add_department') }}">+</button>
                        <button type="button" onclick="openManageModal('department')" class="px-2.5 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition shadow text-xs" title="{{ __('messages.manage_department') }}">📝</button>
                    </div>
                </div>

                <div class="col-span-1">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('messages.job_level') }}</label>
                    <div class="flex gap-1">
                        <select id="position_level" name="position_level" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white" required>
                            <option value="">-- {{ __('messages.select_level') }} --</option>
                            @foreach($jobLevels as $level)
                                <option value="{{ $level->level_number }}" data-id="{{ $level->id }}" data-name="{{ $level->name }}" {{ old('position_level') == $level->level_number ? 'selected' : '' }}>{{ $level->name }} ({{ __('messages.level') }} {{ $level->level_number }})</option>
                            @endforeach
                        </select>
                        <button type="button" onclick="openModal('job-level')" class="px-2.5 py-2 bg-blue-800 text-white font-bold rounded-lg hover:bg-blue-900 transition shadow" title="{{ __('messages.add_job_level') }}">+</button>
                        <button type="button" onclick="openManageModal('job-level')" class="px-2.5 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition shadow text-xs" title="{{ __('messages.manage_job_level') }}">📝</button>
                    </div>
                </div>

                <div class="col-span-1">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('messages.job_title') }}</label>
                    <div class="flex gap-1">
                        <select id="position" name="position" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white" required>
                            <option value="">-- {{ __('messages.select_position') }} --</option>
                            @foreach($jobTitles as $title)
                                <option value="{{ $title->name }}" data-id="{{ $title->id }}" data-level="{{ $title->job_level_id }}" class="hidden" {{ old('position') == $title->name ? 'selected' : '' }}>{{ $title->name }}{{ (isset($title->position_type) && $title->position_type === 'head') ? ' 🔴' : '' }}</option>
                            @endforeach
                        </select>
                        <button type="button" onclick="openModal('job-title')" class="px-2.5 py-2 bg-blue-800 text-white font-bold rounded-lg hover:bg-blue-900 transition shadow" title="{{ __('messages.add_job_title') }}">+</button>
                        <button type="button" onclick="openManageModal('job-title')" class="px-2.5 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition shadow text-xs" title="{{ __('messages.manage_job_title') }}">📝</button>
                    </div>
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-2">{{ __('messages.system_role') }} <span class="text-red-500">*</span></label>
                    <select name="role" class="w-full px-4 py-2 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none bg-blue-50" required>
                        <option value="">-- {{ __('messages.assign_role') }} --</option>
                        <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>User ({{ __('messages.role_user_desc') }})</option>
                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin ({{ __('messages.role_admin_desc') }})</option>
                    </select>
                </div>

                <div class="col-span-2 mt-4">
                    <button type="submit" class="w-full bg-blue-800 text-white py-3 rounded-lg font-bold hover:bg-blue-900 shadow-md transition duration-200">
                        {{ __('messages.save_and_add_member') }}
                    </button>
                </div>
            </form>
        </div>
        
        <p class="mt-6 text-gray-500 text-sm italic">HRC Internal Management System © 2026</p>
    </div>

    <div id="quick-add-modal" class="fixed inset-0 bg-slate-900 bg-opacity-60 hidden items-center justify-center z-50 backdrop-blur-sm">
        <div class="bg-white p-6 rounded-xl shadow-2xl w-full max-w-sm border border-slate-200 mx-4">
            <h3 id="modal-title" class="text-lg font-bold mb-4 text-slate-800">{{ __('messages.add_data') }}</h3>
            
            <form id="quick-add-form" onsubmit="submitQuickAdd(event)">
                <input type="hidden" id="modal-type">
                
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-slate-700 mb-2" id="input-label">{{ __('messages.new_item_name') }}</label>
                    <input type="text" id="modal-input-name" required placeholder="{{ __('messages.placeholder_new_item_name') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <div id="modal-position-type-section" class="mb-4 hidden">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">{{ __('messages.position_type_permission') }}</label>
                    <select id="modal-input-position-type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
                        <option value="employee">employee ({{ __('messages.employee_desc') }})</option>
                        <option value="head">head ({{ __('messages.head_desc') }})</option>
                    </select>
                </div>

                <div id="modal-level-section" class="mb-4 hidden">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">{{ __('messages.bind_job_level') }} (0-5)</label>
                    <select id="modal-input-level" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
                        <option value="0">{{ __('messages.level_0_desc') }}</option>
                        <option value="1">{{ __('messages.level_1_desc') }}</option>
                        <option value="2">{{ __('messages.level_2_desc') }}</option>
                        <option value="3">{{ __('messages.level_3_desc') }}</option>
                        <option value="4">{{ __('messages.level_4_desc') }}</option>
                        <option value="5">{{ __('messages.level_5_desc') }}</option>
                    </select>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeModal()" 
                            class="px-4 py-2 bg-slate-200 text-slate-700 font-semibold rounded-lg hover:bg-slate-300 transition">
                        {{ __('messages.cancel') }}
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-800 text-white font-bold rounded-lg hover:bg-blue-900 transition shadow">
                        {{ __('messages.save_data') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="manage-data-modal" class="fixed inset-0 bg-slate-900 bg-opacity-60 hidden items-center justify-center z-50 backdrop-blur-sm">
        <div class="bg-white p-6 rounded-xl shadow-2xl w-full max-w-md border border-slate-200 mx-4 flex flex-col max-h-[80vh]">
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <h3 id="manage-modal-title" class="text-lg font-bold text-slate-800">{{ __('messages.manage_data') }}</h3>
                <button type="button" onclick="closeManageModal()" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>
            
            <div class="overflow-y-auto flex-1 pr-1" id="manage-items-container">
            </div>

            <div class="flex justify-end gap-3 mt-4 pt-3 border-t">
                <button type="button" onclick="closeManageModal()" 
                        class="px-4 py-2 bg-slate-200 text-slate-700 font-semibold rounded-lg hover:bg-slate-300 transition">
                    {{ __('messages.close_window') }}
                </button>
            </div>
        </div>
    </div>

    <script>
        const levelNamesMapping = {};
        @foreach($jobLevels as $level)
            levelNamesMapping["{{ $level->level_number }}"] = "{{ $level->name }}";
        @endforeach

        const dbJobTitles = [
            @foreach($jobTitles as $title)
                { id: "{{ $title->id }}", name: "{{ $title->name }}", job_level_id: "{{ $title->job_level_id }}", position_type: "{{ $title->position_type ?? '' }}" },
            @endforeach
        ];

        const levelSelect = document.getElementById('position_level');
        const positionSelect = document.getElementById('position');

        function updatePositionDropdown(selectedLevel, selectCustomValue = '') {
            positionSelect.innerHTML = '<option value="">-- {{ __('messages.select_position') }} --</option>';

            if (selectedLevel === '') {
                const uniqueTitles = [...new Set(dbJobTitles.map(t => t.name))];
                uniqueTitles.forEach(name => {
                    const matched = dbJobTitles.find(t => t.name === name);
                    appendOption(matched.name, matched.id, matched.job_level_id, matched.position_type);
                });
                return;
            }

            const levelText = levelNamesMapping[selectedLevel] || '';
            const activeLevelOption = Array.from(levelSelect.options).find(opt => opt.value === String(selectedLevel));
            const targetJobLevelId = activeLevelOption ? activeLevelOption.getAttribute('data-id') : null;

            const filteredTitles = dbJobTitles.filter(title => {
                if (targetJobLevelId && String(title.job_level_id) === String(targetJobLevelId)) {
                    return true;
                }

                if (!title.job_level_id || title.job_level_id === 'NULL' || title.job_level_id === '') {
                    if (levelText.includes('ประธานเจ้าหน้าที่บริหาร') || selectedLevel == '0') {
                        return title.name.includes('ประธานเจ้าหน้าที่บริหาร') || title.name.toLowerCase().includes('ceo');
                    }
                    if (levelText.includes('ประธานสายงาน') || selectedLevel == '1') {
                        return title.name.includes('ประธานสายงาน') && !title.name.includes('ประธานเจ้าหน้าที่บริหาร');
                    }
                    if (levelText.includes('ผู้อำนวยการ') || selectedLevel == '2') {
                        return title.name.includes('ผู้อำนวยการ');
                    }
                    if (levelText.includes('ผู้จัดการกลุ่มงาน') || selectedLevel == '3') {
                        return title.name.includes('ผู้จัดการกลุ่มงาน');
                    }
                    if (levelText.includes('ผู้จัดการฝ่าย') || selectedLevel == '4') {
                        return title.name.includes('ผู้จัดการฝ่าย') || (title.name.includes('ผู้จัดการ') && !title.name.includes('กลุ่มงาน'));
                    }
                    if (levelText.includes('เจ้าหน้าที่') || levelText.includes('พนักงาน') || selectedLevel == '5') {
                        return (title.name.includes('เจ้าหน้าที่') || title.name.includes('พนักงาน')) && 
                               !title.name.includes('ประธาน') && 
                               !title.name.includes('ผู้จัดการ') && 
                               !title.name.includes('ผู้อำนวยการ');
                    }
                }
                return false;
            });

            const finalUniqueTitles = [];
            const seenNames = new Set();
            filteredTitles.forEach(t => {
                if (!seenNames.has(t.name)) {
                    seenNames.add(t.name);
                    finalUniqueTitles.push(t);
                }
            });

            finalUniqueTitles.forEach(function(title) {
                appendOption(title.name, title.id, title.job_level_id, title.position_type);
            });

            if (selectCustomValue) {
                positionSelect.value = selectCustomValue;
            }
        }

        function appendOption(name, id, jobLevelId, positionType) {
            const option = document.createElement('option');
            option.value = name;
            option.text = positionType === 'head' ? name + ' 🔴' : name;
            option.setAttribute('data-id', id || 'default');
            option.setAttribute('data-level', jobLevelId || '');
            positionSelect.appendChild(option);
        }

        levelSelect.addEventListener('change', function() {
            updatePositionDropdown(this.value);
        });

        let currentType = '';

        function openModal(type) {
            currentType = type;
            document.getElementById('modal-type').value = type;
            
            if (type === 'job-title' && levelSelect.value === '') {
                alert("{{ __('messages.alert_select_level_first') }}");
                return;
            }

            document.getElementById('quick-add-modal').style.display = 'flex';
            document.getElementById('modal-input-name').value = '';

            const titles = {
                'branch': "{{ __('messages.add_branch_new') }}",
                'department': "{{ __('messages.add_department_new') }}",
                'job-level': "{{ __('messages.add_job_level_new') }}",
                'job-title': "{{ __('messages.add_job_title_new') }}"
            };
            document.getElementById('modal-title').innerText = titles[type];
            
            if(type === 'job-level') {
                document.getElementById('modal-level-section').classList.remove('hidden');
                document.getElementById('modal-position-type-section').classList.add('hidden');
            } else if(type === 'job-title') {
                document.getElementById('modal-position-type-section').classList.remove('hidden');
                document.getElementById('modal-level-section').classList.add('hidden');
            } else {
                document.getElementById('modal-level-section').classList.add('hidden');
                document.getElementById('modal-position-type-section').classList.add('hidden');
            }
        }

        function closeModal() {
            document.getElementById('quick-add-modal').style.display = 'none';
        }

        function submitQuickAdd(event) {
            event.preventDefault();
            
            const nameValue = document.getElementById('modal-input-name').value;
            const levelValue = document.getElementById('modal-input-level').value;
            const positionTypeValue = document.getElementById('modal-input-position-type').value; 
            const activeFormLevel = levelSelect.value; 
            
            const activeLevelOption = Array.from(levelSelect.options).find(opt => opt.value === String(activeFormLevel));
            const activeFormJobLevelId = activeLevelOption ? activeLevelOption.getAttribute('data-id') : activeFormLevel;

            let url = `/admin/quick-add/${currentType}`;
            let bodyData = { name: nameValue };
            
            if (currentType === 'job-title') {
                bodyData.job_level_id = activeFormJobLevelId;
                bodyData.position_level = activeFormLevel;
                bodyData.position_type = positionTypeValue; 
            }
            if (currentType === 'job-level') {
                bodyData.level_number = levelValue;
            }

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify(bodyData)
            })
            .then(response => {
                if(!response.ok) throw new Error('Duplicate');
                return response.json();
            })
            .then(res => {
                if (res.success) {
                    if (currentType === 'job-level') {
                        const selectEl = document.getElementById('position_level');
                        const newOption = document.createElement('option');
                        newOption.value = res.data.level_number;
                        newOption.text = `${res.data.name} ({{ __('messages.level') }} ${res.data.level_number})`;
                        newOption.setAttribute('data-id', res.data.id || '');
                        newOption.setAttribute('data-name', res.data.name || '');
                        selectEl.add(newOption);
                        selectEl.value = newOption.value;
                        
                        levelNamesMapping[res.data.level_number] = res.data.name;
                        updatePositionDropdown(res.data.level_number);
                    } 
                    else if (currentType === 'job-title') {
                        dbJobTitles.push({ id: res.data.id || '', name: res.data.name, job_level_id: res.data.job_level_id || activeFormJobLevelId, position_type: res.data.position_type || positionTypeValue });
                        updatePositionDropdown(activeFormLevel, res.data.name);
                    } 
                    else {
                        let selectId = currentType === 'branch' ? 'select-branch' : 'select-department';
                        const selectEl = document.getElementById(selectId);
                        const newOption = document.createElement('option');
                        newOption.value = res.data.name;
                        newOption.text = res.data.name;
                        newOption.setAttribute('data-id', res.data.id || '');
                        selectEl.add(newOption);
                        selectEl.value = newOption.value;
                    }
                    closeModal();
                } else {
                    alert("{{ __('messages.alert_save_error') }}");
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert("{{ __('messages.alert_duplicate_or_error') }}");
            });
        }

        let manageType = '';

        function openManageModal(type) {
            manageType = type;
            const container = document.getElementById('manage-items-container');
            container.innerHTML = '';
            
            const titles = {
                'branch': "{{ __('messages.manage_branch_data') }}",
                'department': "{{ __('messages.manage_department_data') }}",
                'job-level': "{{ __('messages.manage_job_level_data') }}",
                'job-title': "{{ __('messages.manage_job_title_data') }}"
            };
            document.getElementById('manage-modal-title').innerText = titles[type];

            let options = [];
            
            if (type === 'job-title') {
                const activeLevel = levelSelect.value;
                if (!activeLevel) {
                    alert("{{ __('messages.alert_select_level_first_manage') }}");
                    return;
                }
                options = Array.from(positionSelect.options).filter(opt => opt.value !== "" && opt.getAttribute('data-id') !== 'default');
            } else {
                let sourceSelectId = '';
                if (type === 'branch') sourceSelectId = 'select-branch';
                if (type === 'department') sourceSelectId = 'select-department';
                if (type === 'job-level') sourceSelectId = 'position_level';
                
                const sourceSelect = document.getElementById(sourceSelectId);
                options = Array.from(sourceSelect.options).filter(opt => opt.value !== "");
            }

            if (options.length === 0) {
                container.innerHTML = `<p class="text-center text-gray-400 py-6 text-sm italic">--- {{ __('messages.no_data_found') }} ---</p>`;
            } else {
                options.forEach(opt => {
                    const id = opt.getAttribute('data-id') || '';
                    const name = opt.text;
                    let value = opt.value;

                    if (type === 'job-level') {
                        value = opt.getAttribute('data-name') || opt.value;
                    }

                    if (!id || id === 'default') return;

                    const row = document.createElement('div');
                    row.className = "flex justify-between items-center p-2.5 hover:bg-slate-50 border-b border-slate-100 last:border-0 rounded-lg gap-2";
                    row.innerHTML = `
                        <input type="text" id="input-manage-${id}" value="${value}" 
                               class="flex-1 px-3 py-1.5 text-sm border border-slate-200 rounded focus:ring-1 focus:ring-blue-500 focus:outline-none text-slate-700 bg-slate-50">
                        <div class="flex gap-1.5">
                            <button type="button" onclick="editManageItem('${id}', '${value}')" class="px-2.5 py-1 bg-amber-500 text-white rounded text-xs hover:bg-amber-600 transition">{{ __('messages.edit') }}</button>
                            <button type="button" onclick="deleteManageItem('${id}', '${value}')" class="px-2.5 py-1 bg-red-600 text-white rounded text-xs hover:bg-red-700 transition">{{ __('messages.delete') }}</button>
                        </div>
                    `;
                    container.appendChild(row);
                });
                
                if(container.children.length === 0) {
                     container.innerHTML = `<p class="text-center text-gray-400 py-6 text-sm italic">--- {{ __('messages.default_delete_unsupported') }} ---</p>`;
                }
            }

            document.getElementById('manage-data-modal').style.display = 'flex';
        }

        function closeManageModal() {
            document.getElementById('manage-data-modal').style.display = 'none';
        }

        function editManageItem(id, originalValue) {
            const newName = document.getElementById(`input-manage-${id}`).value;
            if(!newName.trim()) return alert("{{ __('messages.alert_enter_valid_name') }}");
            
            if(!confirm(`{{ __('messages.confirm_change_name_from') }} "${originalValue}" {{ __('messages.to') }} "${newName}"?`)) return;

            fetch(`/admin/manage-update/${manageType}/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({ name: newName })
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                alert("{{ __('messages.alert_update_success') }}");
                location.reload(); 
            })
            .catch(error => {
                console.error('Error:', error);
                alert("{{ __('messages.alert_update_error') }}");
            });
        }

        function deleteManageItem(id, value) {
            if(!confirm(`⚠️ {{ __('messages.confirm_delete_warning') }} "${value}"?`)) return;

            fetch(`/admin/manage-delete/${manageType}/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw new Error(err.error || 'Server Error') });
                }
                return response.json();
            })
            .then(data => {
                alert("{{ __('messages.alert_delete_success') }}");
                location.reload(); 
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ ' + error.message);
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const savedLevel = levelSelect.value;
            const savedPosition = "{{ old('position') }}";
            
            updatePositionDropdown(savedLevel);
            
            if (savedLevel && savedPosition) {
                positionSelect.value = savedPosition;
            }
        });
    </script>
</body>
</html>