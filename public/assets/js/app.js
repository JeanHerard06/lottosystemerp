// Sprint 19 - Global UI helpers
(function(){
  function wrapTables(){
    document.querySelectorAll('table').forEach(function(table){
      if (table.closest('.table-responsive') || table.dataset.noResponsive === '1') return;
      var wrapper = document.createElement('div');
      wrapper.className = 'table-responsive bg-white shadow rounded-xl';
      table.parentNode.insertBefore(wrapper, table);
      wrapper.appendChild(table);
      table.classList.add('min-w-full');
    });
  }

  function activeSidebar(){
    var path = window.location.pathname.replace(/\/+$/, '');
    document.querySelectorAll('aside a[href]').forEach(function(link){
      var href = link.getAttribute('href');
      if (!href) return;
      var linkPath = href.replace(/\/+$/, '');
      if (linkPath === path) link.classList.add('sidebar-link-active');
    });
  }

  function confirmDanger(){
    document.querySelectorAll('[data-confirm]').forEach(function(el){
      el.addEventListener('click', function(e){
        var msg = el.getAttribute('data-confirm') || 'Confirmer cette action ?';
        if (!window.confirm(msg)) e.preventDefault();
      });
    });
  }

  function autoDismissAlerts(){
    document.querySelectorAll('[data-auto-dismiss]').forEach(function(el){
      var delay = parseInt(el.getAttribute('data-auto-dismiss'), 10) || 4500;
      setTimeout(function(){ el.style.display = 'none'; }, delay);
    });
  }

  document.addEventListener('DOMContentLoaded', function(){
    wrapTables();
    activeSidebar();
    confirmDanger();
    autoDismissAlerts();
  });
})();
