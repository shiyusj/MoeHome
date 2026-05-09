/**
 * MoeHome 后台管理 - 公共脚本
 */

(function() {
    'use strict';

    window.Admin = {
        api: function(url, options = {}) {
            options = Object.assign({
                method: 'GET',
                data: null,
                headers: {},
                success: null,
                error: null
            }, options);

            return new Promise(function(resolve, reject) {
                var xhr = new XMLHttpRequest();
                xhr.open(options.method, url, true);

                xhr.setRequestHeader('Content-Type', 'application/json');

                Object.keys(options.headers).forEach(function(key) {
                    xhr.setRequestHeader(key, options.headers[key]);
                });

                xhr.onload = function() {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (xhr.status >= 200 && xhr.status < 300) {
                            if (options.success) options.success(response);
                            resolve(response);
                        } else {
                            if (options.error) options.error(response);
                            reject(response);
                        }
                    } catch (e) {
                        reject({ error: 'Invalid JSON response' });
                    }
                };

                xhr.onerror = function() {
                    reject({ error: 'Network error' });
                };

                if (options.data) {
                    xhr.send(JSON.stringify(options.data));
                } else {
                    xhr.send();
                }
            });
        },

        toast: function(message, type = 'info', duration = 3000) {
            var container = document.querySelector('.toast-container');
            if (!container) {
                container = document.createElement('div');
                container.className = 'toast-container';
                document.body.appendChild(container);
            }

            var toast = document.createElement('div');
            toast.className = 'toast toast-' + type;
            toast.innerHTML = '<i class="fas fa-' + this.getToastIcon(type) + '"></i><span>' + message + '</span>';

            container.appendChild(toast);

            setTimeout(function() {
                toast.style.animation = 'slideIn 0.3s ease reverse';
                setTimeout(function() {
                    container.removeChild(toast);
                }, 300);
            }, duration);
        },

        getToastIcon: function(type) {
            var icons = {
                success: 'check-circle',
                error: 'exclamation-circle',
                warning: 'exclamation-triangle',
                info: 'info-circle'
            };
            return icons[type] || 'info-circle';
        },

        confirm: function(message, callback) {
            if (confirm(message)) {
                callback();
            }
        },

        loading: function(show) {
            var overlay = document.querySelector('.loading-overlay');
            if (show) {
                if (!overlay) {
                    overlay = document.createElement('div');
                    overlay.className = 'loading-overlay';
                    overlay.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i></div>';
                    document.body.appendChild(overlay);
                }
                overlay.style.display = 'flex';
            } else {
                if (overlay) {
                    overlay.style.display = 'none';
                }
            }
        },

        submitForm: function(form, callback) {
            var formData = new FormData(form);
            var data = {};

            formData.forEach(function(value, key) {
                if (data[key]) {
                    if (!Array.isArray(data[key])) {
                        data[key] = [data[key]];
                    }
                    data[key].push(value);
                } else {
                    data[key] = value;
                }
            });

            this.loading(true);

            var csrfToken = form.querySelector('input[name="csrf_token"]');
            var headers = {};
            if (csrfToken) {
                headers['X-CSRF-Token'] = csrfToken.value;
            }

            this.api(form.action, {
                method: 'POST',
                data: data,
                headers: headers
            }).then(function(response) {
                this.loading(false);
                if (response.success) {
                    this.toast(response.message || '保存成功', 'success');
                    if (callback) callback(response);
                } else {
                    this.toast(response.error || '保存失败', 'error');
                }
            }.bind(this)).catch(function(error) {
                this.loading(false);
                this.toast(error.error || '请求失败', 'error');
            }.bind(this));
        },

        toggleSwitch: function(checkbox, url) {
            var enabled = checkbox.checked;
            var label = checkbox.closest('.form-switch');

            this.api(url, {
                method: 'POST',
                data: { enabled: enabled }
            }).then(function(response) {
                this.toast(response.message || (enabled ? '已启用' : '已禁用'), 'success');
            }.bind(this)).catch(function(error) {
                checkbox.checked = !enabled;
                this.toast(error.error || '操作失败', 'error');
            }.bind(this));
        },

        saveConfig: function(category, data, callback) {
            this.loading(true);

            this.api('api/config.php', {
                method: 'POST',
                data: {
                    action: 'save',
                    category: category,
                    data: data
                }
            }).then(function(response) {
                this.loading(false);
                this.toast(response.message || '保存成功', 'success');
                if (callback) callback(response);
            }.bind(this)).catch(function(error) {
                this.loading(false);
                this.toast(error.error || '保存失败', 'error');
            }.bind(this));
        },

        initTheme: function() {
            var theme = localStorage.getItem('moehome-theme');
            var themeData = theme ? JSON.parse(theme) : null;
            var mode = themeData?.mode || 'light';

            document.documentElement.setAttribute('data-theme', mode);

            var toggleBtns = document.querySelectorAll('[data-theme-toggle]');
            toggleBtns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var newMode = mode === 'light' ? 'dark' : 'light';
                    mode = newMode;
                    document.documentElement.setAttribute('data-theme', newMode);

                    localStorage.setItem('moehome-theme', JSON.stringify({
                        mode: newMode,
                        scheme: themeData?.scheme || (newMode === 'light' ? 'coralOrange' : 'cyberGreen')
                    }));
                });
            });
        },

        initSidebar: function() {
            var sidebar = document.querySelector('.sidebar');
            var toggleBtn = document.querySelector('.sidebar-toggle');

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('open');
                });
            }

            var currentPath = window.location.pathname;
            var navLinks = document.querySelectorAll('.nav-item');

            navLinks.forEach(function(link) {
                var href = link.getAttribute('href');
                if (href && currentPath.endsWith(href)) {
                    link.classList.add('active');
                }
            });
        },

        initForms: function() {
            var forms = document.querySelectorAll('form[data-ajax]');

            forms.forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    Admin.submitForm(form);
                });
            });

            var switches = document.querySelectorAll('.switch input[data-toggle-url]');
            switches.forEach(function(sw) {
                sw.addEventListener('change', function() {
                    Admin.toggleSwitch(this, this.dataset.toggleUrl);
                });
            });
        },

        initColorPicker: function() {
            var swatches = document.querySelectorAll('.color-swatch');

            swatches.forEach(function(swatch) {
                swatch.addEventListener('click', function() {
                    var parent = this.closest('.color-picker');
                    if (parent) {
                        parent.querySelectorAll('.color-swatch').forEach(function(s) {
                            s.classList.remove('active');
                        });
                    }
                    this.classList.add('active');

                    var input = document.querySelector('input[name="' + (parent.dataset.input || 'color') + '"]');
                    if (input) {
                        input.value = this.dataset.color;
                    }
                });
            });
        },

        init: function() {
            this.initTheme();
            this.initSidebar();
            this.initForms();
            this.initColorPicker();
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        Admin.init();
    });
})();
