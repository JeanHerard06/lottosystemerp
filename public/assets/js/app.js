// Global responsive UI helpers
(function () {
  'use strict';

  function normalizeText(value) {
    return (value || '').replace(/\s+/g, ' ').trim();
  }

  function enhanceTables() {
    document.querySelectorAll('table').forEach(function (table, tableIndex) {
      if (table.dataset.noResponsive === '1') return;

      var headers = Array.from(table.querySelectorAll('thead th')).map(function (th) {
        return normalizeText(th.textContent);
      });

      table.classList.add('responsive-data-table');
      table.setAttribute('data-responsive-table', String(tableIndex + 1));

      table.querySelectorAll('tbody tr').forEach(function (row) {
        var cells = Array.from(row.children).filter(function (cell) {
          return cell.tagName === 'TD' || cell.tagName === 'TH';
        });

        // Empty-state rows keep their normal centered presentation.
        if (cells.length === 1 && Number(cells[0].getAttribute('colspan') || 1) > 1) {
          row.classList.add('responsive-empty-row');
          return;
        }

        row.classList.add('responsive-record-card');
        cells.forEach(function (cell, index) {
          if (!cell.dataset.label) {
            cell.dataset.label = headers[index] || ('Champ ' + (index + 1));
          }
          if ((headers[index] || '').toLowerCase().includes('action')) {
            cell.classList.add('responsive-actions-cell');
          }
        });
      });

      // Keep horizontal scroll on tablets; mobile cards are handled by CSS.
      if (!table.closest('.table-responsive')) {
        var wrapper = document.createElement('div');
        wrapper.className = 'table-responsive bg-white shadow rounded-xl';
        table.parentNode.insertBefore(wrapper, table);
        wrapper.appendChild(table);
      }
      table.classList.add('min-w-full');
    });
  }

  function enhancePageHeaders() {
    var content = document.querySelector('main > div:last-of-type');
    if (!content) return;

    Array.from(content.children).slice(0, 3).forEach(function (element) {
      if (!element.classList.contains('flex') || !element.classList.contains('justify-between')) return;
      element.classList.add('responsive-page-header');
      Array.from(element.children).forEach(function (child) {
        if (child.tagName === 'A' || child.tagName === 'BUTTON') {
          child.classList.add('responsive-primary-action');
        }
      });
    });
  }

  function activeSidebar() {
    var path = window.location.pathname.replace(/\/+$/, '');
    document.querySelectorAll('aside a[href]').forEach(function (link) {
      var href = link.getAttribute('href');
      if (!href) return;
      var linkPath = href.replace(/\/+$/, '');
      if (linkPath === path) link.classList.add('sidebar-link-active');
    });
  }

  function closeSidebarAfterNavigation() {
    document.querySelectorAll('#mobileSidebar a[href]').forEach(function (link) {
      link.addEventListener('click', function () {
        if (typeof window.closeMobileSidebar === 'function') window.closeMobileSidebar();
      });
    });
  }

  function confirmDanger() {
    document.querySelectorAll('[data-confirm]').forEach(function (element) {
      element.addEventListener('click', function (event) {
        var message = element.getAttribute('data-confirm') || 'Confirmer cette action ?';
        if (!window.confirm(message)) event.preventDefault();
      });
    });
  }

  function autoDismissAlerts() {
    document.querySelectorAll('[data-auto-dismiss]').forEach(function (element) {
      var delay = parseInt(element.getAttribute('data-auto-dismiss'), 10) || 4500;
      setTimeout(function () { element.style.display = 'none'; }, delay);
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    enhanceTables();
    enhancePageHeaders();
    activeSidebar();
    closeSidebarAfterNavigation();
    confirmDanger();
    autoDismissAlerts();
  });
})();

// Web Responsive UI — Phase 2
(function () {
  'use strict';

  function setButtonState(button, expanded) {
    button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    var label = button.querySelector('[data-filter-label]');
    if (label) label.textContent = expanded ? 'Masquer les filtres' : 'Afficher les filtres';
  }

  function enhanceFilterPanels() {
    document.querySelectorAll('form').forEach(function (form) {
      if (form.dataset.noResponsiveFilter === '1') return;
      var fields = form.querySelectorAll('input, select, textarea');
      var submit = form.querySelector('button[type="submit"], input[type="submit"]');
      if (fields.length < 2 || !submit) return;

      var parent = form.parentElement;
      if (!parent || parent.classList.contains('app-filter-panel')) return;
      var text = (submit.textContent || submit.value || '').toLowerCase();
      var looksLikeFilter = /filtr|recherch|search|appliqu/.test(text) || form.method.toLowerCase() === 'get';
      if (!looksLikeFilter) return;

      parent.classList.add('app-filter-panel');
      parent.dataset.mobileCollapsible = '1';
      form.classList.add('app-filter-content');

      var toggle = document.createElement('button');
      toggle.type = 'button';
      toggle.className = 'app-filter-toggle btn bg-gray-100 text-gray-800';
      toggle.innerHTML = '<span data-filter-label>Afficher les filtres</span><span class="filter-chevron" aria-hidden="true">⌄</span>';
      toggle.addEventListener('click', function () {
        var open = !parent.classList.contains('is-open');
        parent.classList.toggle('is-open', open);
        setButtonState(toggle, open);
      });
      parent.insertBefore(toggle, form);
      setButtonState(toggle, false);
    });
  }

  function enhanceForms() {
    document.querySelectorAll('form').forEach(function (form) {
      form.querySelectorAll('.grid').forEach(function (grid) {
        if (grid.querySelector('input, select, textarea')) grid.classList.add('responsive-form-grid');
      });

      var actionRows = Array.from(form.querySelectorAll('.flex')).filter(function (row) {
        return row.querySelector('button, input[type="submit"], a');
      });
      actionRows.forEach(function (row) { row.classList.add('responsive-form-actions'); });
    });
  }

  function enhanceModals() {
    document.querySelectorAll('[role="dialog"], .modal, [data-modal]').forEach(function (modal) {
      modal.setAttribute('role', 'dialog');
      modal.setAttribute('aria-modal', 'true');
      var panel = modal.querySelector('.bg-white, .modal-content, [data-modal-panel]');
      if (panel) panel.classList.add('app-modal-panel');
    });
  }

  function enhancePagination() {
    document.querySelectorAll('.pagination, nav[aria-label*="pagination" i]').forEach(function (pagination) {
      pagination.classList.add('app-pagination');
    });
  }

  function addAccessibleTableDescriptions() {
    document.querySelectorAll('.responsive-data-table').forEach(function (table, index) {
      if (!table.getAttribute('aria-label') && !table.getAttribute('aria-labelledby')) {
        table.setAttribute('aria-label', 'Tableau de données ' + (index + 1));
      }
    });
  }

  function markScrollableRegions() {
    document.querySelectorAll('.table-responsive, [role="tablist"]').forEach(function (region) {
      region.setAttribute('tabindex', '0');
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    enhanceFilterPanels();
    enhanceForms();
    enhanceModals();
    enhancePagination();
    addAccessibleTableDescriptions();
    markScrollableRegions();
  });
})();

// RC1.2 — Enterprise UI normalization
(function () {
  'use strict';

  function normalizePanels() {
    document.querySelectorAll('.overflow-x-auto.bg-white, .bg-white.rounded.shadow.overflow-x-auto').forEach(function (panel) {
      if (panel.querySelector('table')) panel.classList.add('ui-table-panel');
    });

    document.querySelectorAll('form.bg-white').forEach(function (form) {
      var text = (form.textContent || '').toLowerCase();
      if (form.method && (form.method.toLowerCase() === 'get' || /filtr|recherch|search/.test(text))) {
        form.classList.add('ui-filter-form');
        if (form.parentElement && !form.parentElement.classList.contains('ui-filter-bar')) {
          var wrapper = document.createElement('div');
          wrapper.className = 'ui-filter-bar';
          form.parentNode.insertBefore(wrapper, form);
          wrapper.appendChild(form);
        }
      }
    });
  }

  function normalizeButtons() {
    document.querySelectorAll('a,button,input[type="submit"]').forEach(function (element) {
      if (element.classList.contains('ui-btn') || element.closest('aside')) return;
      var classes = element.className || '';
      if (!/(bg-black|bg-yellow-500|bg-blue-600|bg-green-600|bg-red-600)/.test(classes)) return;
      element.classList.add('ui-btn');
      if (classes.includes('bg-green-600')) element.classList.add('ui-btn-success');
      else if (classes.includes('bg-red-600')) element.classList.add('ui-btn-danger');
      else if (classes.includes('bg-yellow-500')) element.classList.add('ui-btn-warning');
      else if (classes.includes('bg-blue-600')) element.classList.add('ui-btn-primary');
      else element.classList.add('ui-btn-primary');
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    normalizePanels();
    normalizeButtons();
  });
})();
