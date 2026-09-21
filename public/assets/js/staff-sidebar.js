(() => {
    'use strict';
    const sidebar = document.getElementById('staff-work-sidebar');
    if (!sidebar) return;
    const selector = document.getElementById('staff-sidebar-role');
    const items = [...sidebar.querySelectorAll('[data-sidebar-role]')];
    const links = [...sidebar.querySelectorAll('a.pc-link[href]')];
    const storageKey = `epostgrad.sidebar-role.${sidebar.dataset.staffId}`;
    let counts = {};
    try { counts = JSON.parse(document.getElementById('staff-work-counts').textContent); } catch (_) { /* Navigation remains usable. */ }
    const totals = {};
    const cleanPath = value => new URL(value, location.href).pathname.replace(/\/$/, '');
    const currentPath = cleanPath(location.href);
    let currentLink = null;
    function badge(link, count) {
        if (!count) return;
        const element = document.createElement('span');
        element.className = 'staff-work-badge';
        element.textContent = count > 99 ? '99+' : String(count);
        element.title = `${count} pending items across all semesters`;
        element.setAttribute('aria-label', `${count} pending items`);
        const arrow = link.querySelector('.pc-arrow');
        link.insertBefore(element, arrow);
    }
    function renderCounts() {
        sidebar.querySelectorAll('.staff-work-badge').forEach(element => element.remove());
        Object.keys(totals).forEach(key => delete totals[key]);
    links.forEach(link => {
        if (!/^https?:/i.test(link.href)) return;
        link.dataset.workUrl ||= link.href;
        const count = Number(counts[link.dataset.workUrl || link.href] || 0);
        const role = link.closest('[data-sidebar-role]')?.dataset.sidebarRole;
        if (role) totals[role] = (totals[role] || 0) + count;
        badge(link, count);
        link.dataset.pendingCount = String(count);
        if (cleanPath(link.href) === currentPath) currentLink = link;
        if (count) {
            const url = new URL(link.href);
            url.searchParams.set('work_scope', 'all');
            link.href = url.href;
        }
    });
    // A collapsed module still exposes the pending work within it.
    sidebar.querySelectorAll('.pc-hasmenu').forEach(menu => {
        const count = [...menu.querySelectorAll('a[data-pending-count]')]
            .reduce((sum, link) => sum + Number(link.dataset.pendingCount), 0);
        const link = menu.querySelector(':scope > a');
        if (link) badge(link, count);
    });
    }
    renderCounts();
    function selectRole(role, remember = true) {
        if (!selector || ![...selector.options].some(option => option.value === role)) return;
        selector.value = role;
        items.forEach(item => { item.hidden = item.dataset.sidebarRole !== role; });
        if (remember) { try { localStorage.setItem(storageKey, role); } catch (_) { /* Storage may be disabled. */ } }
        sidebar.querySelectorAll('[data-dashboard-link]').forEach(link => {
            const url = new URL(sidebar.dataset.dashboardUrl, location.href);
            url.searchParams.set('dashboard_role', role);
            link.href = url.href;
        });
        const otherRoles = document.getElementById('staff-other-roles');
        otherRoles.replaceChildren();
        [...selector.options].forEach(option => {
            const count = totals[option.value] || 0;
            option.textContent = option.dataset.label + (count ? ` (${count})` : '');
            if (option.value === role || !count) return;
            const button = document.createElement('button');
            button.type = 'button';
            const label = document.createElement('span');
            label.textContent = option.dataset.label;
            const indicator = document.createElement('span');
            indicator.className = 'staff-work-badge';
            indicator.textContent = count > 99 ? '99+' : String(count);
            button.append(label, indicator);
            button.setAttribute('aria-label', `Switch to ${option.dataset.label}, ${count} pending items`);
            button.addEventListener('click', () => { selectRole(option.value); selector.focus(); });
            otherRoles.appendChild(button);
        });
    }
    sidebar.addEventListener('click', event => {
        const link = event.target.closest('a.pc-link');
        if (!link || !link.parentElement.classList.contains('pc-hasmenu')) return;
        event.preventDefault();
        event.stopImmediatePropagation();
        const menu = link.parentElement;
        const open = !menu.classList.contains('pc-trigger');
        menu.classList.toggle('pc-trigger', open);
        link.setAttribute('aria-expanded', String(open));
        const submenu = menu.querySelector(':scope > .pc-submenu');
        if (submenu) submenu.style.display = open ? 'block' : 'none';
    }, true);

    let savedRole;
    try { savedRole = localStorage.getItem(storageKey); } catch (_) { /* Fall back to the available role. */ }
    const routeRole = currentLink?.closest('[data-sidebar-role]')?.dataset.sidebarRole || sidebar.dataset.routeRole;
    if (selector) {
        const validSaved = [...selector.options].some(option => option.value === savedRole);
        const initialRole = routeRole || (validSaved ? savedRole : selector.value);
        selectRole(initialRole, false);
        const onDashboard = cleanPath(location.href) === cleanPath(sidebar.dataset.dashboardUrl);
        const dashboardRole = new URLSearchParams(location.search).get('dashboard_role');
        if (onDashboard && !dashboardRole && validSaved) {
            const url = new URL(location.href);
            url.searchParams.set('dashboard_role', savedRole);
            location.replace(url.href);
            return;
        }
        selector.addEventListener('change', () => {
            selectRole(selector.value);
            if (!onDashboard) return;
            const url = new URL(location.href);
            url.searchParams.set('dashboard_role', selector.value);
            location.assign(url.href);
        });
    }
    if (currentLink) {
        currentLink.setAttribute('aria-current', 'page');
        currentLink.closest('.pc-item')?.classList.add('active');
        let parent = currentLink.parentElement.parentElement;
        while (parent && parent !== sidebar) {
            if (parent.classList.contains('pc-hasmenu')) {
                parent.classList.add('pc-trigger');
                const submenu = parent.querySelector(':scope > .pc-submenu');
                if (submenu) submenu.style.display = 'block';
            }
            parent = parent.parentElement;
        }
    }
    let refreshTimer;
    let refreshing = false;
    async function refreshCounts() {
        if (refreshing) return;
        refreshing = true;
        try {
            const response = await fetch(sidebar.dataset.countsUrl, { credentials: 'same-origin', headers: { Accept: 'application/json' } });
            if (!response.ok) return;
            counts = await response.json();
            renderCounts();
            if (selector) selectRole(selector.value, false);
        } catch (_) { /* Keep the last known counts until the next successful refresh. */ }
        finally { refreshing = false; }
    }
    window.addEventListener('load', () => {
        if (window.jQuery) window.jQuery(document).on('ajaxSuccess.staffSidebar', (_event, _xhr, settings) => {
            if (!/^(POST|PUT|PATCH|DELETE)$/i.test(settings.type || 'GET')) return;
            clearTimeout(refreshTimer);
            refreshTimer = setTimeout(refreshCounts, 500);
        });
    }, { once: true });
    // Badge navigation must not leave older work hidden by a page's default semester filter.
    if (new URLSearchParams(location.search).get('work_scope') === 'all') {
        const showAllSemesters = () => {
            const filters = ['fil_semester_id', 'fil_faculty_id', 'fil_programme_id', 'fil_status'];
            const cleared = filters.map(id => document.getElementById(id)).filter(filter => filter && [...filter.options].some(option => option.value === ''));
            cleared.forEach(filter => { filter.value = ''; });
            if (cleared.length && window.jQuery) window.jQuery(cleared[0]).trigger('change');
        };
        if (document.readyState === 'complete') showAllSemesters();
        else window.addEventListener('load', showAllSemesters, { once: true });
    }
})();
