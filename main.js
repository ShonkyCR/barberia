// ==========================================
// 1. SISTEMA DE MENSAJES (TOASTS)
// ==========================================
function mostrarMensaje(texto, tipo = "success") {
  const container = document.getElementById("toast-container");
  if (!container) return;

  const toast = document.createElement("div");
  toast.className = `toast ${tipo === "error" ? "error" : ""}`;
  const icono = tipo === "success" ? "fa-check-circle" : "fa-exclamation-circle";

  toast.innerHTML = `
        <i class="fas ${icono}"></i>
        <span>${texto}</span>
    `;

  container.appendChild(toast);
  setTimeout(() => {
    toast.remove();
  }, 3000);
}

// ==========================================
// 2. UTILIDAD: CONVERTIR A 12 HORAS
// ==========================================
function formatear12h(hora24) {
  if (!hora24) return "---";
  
  if (hora24.toLowerCase().includes("am") || hora24.toLowerCase().includes("pm")) {
    return hora24;
  }

  let partes = hora24.split(":");
  if (partes.length < 2) return hora24;

  let horas = parseInt(partes[0]);
  let minutos = partes[1].substring(0, 2);

  const ampm = horas >= 12 ? "PM" : "AM";
  horas = horas % 12;
  horas = horas ? horas : 12;
  return `${horas}:${minutos} ${ampm}`;
}

// ==========================================
// 3. GESTIÓN DE PERFIL (COMÚN)
// ==========================================
function abrirModalPerfil() {
  const modal = document.getElementById("modal-perfil");
  if (!modal) return;
  modal.style.display = "flex";

  fetch("obtener_perfil.php")
    .then((res) => res.json())
    .then((user) => {
      document.getElementById("perfil-nombre").value = user.nombre;
      document.getElementById("perfil-telefono").value = user.telefono || "";
    })
    .catch((err) => console.error("Error al cargar perfil:", err));
}

function cerrarModalPerfil() {
  const modal = document.getElementById("modal-perfil");
  if (modal) modal.style.display = "none";
}

// ==========================================
// 4. FUNCIONES PARA CLIENTES (clientes.php)
// ==========================================
function actualizarHorasDisponibles() {
  const fecha = document.getElementById("fecha-cita").value;
  const barbero = document.getElementById("barbero") ? document.getElementById("barbero").value : '';
  const selectHora = document.getElementById("hora-cita");

  if (!fecha || !selectHora) return;

  const horarioBarberia = ["09:00", "09:30", "10:00", "10:30", "11:00", "11:30", "14:00", "14:30", "15:00", "15:30", "16:00", "16:30"];

  fetch(`horas_ocupadas.php?fecha=${fecha}&barbero=${barbero}`)
    .then((res) => res.json())
    .then((horasOcupadas) => {
      selectHora.innerHTML = '<option value="">-- Selecciona hora --</option>';
      const ocupadasLimpio = horasOcupadas.map((h) => h.substring(0, 5));

      const ahora = new Date();
      const horaActualStr = ahora.getHours().toString().padStart(2, "0") + ":" + ahora.getMinutes().toString().padStart(2, "0");

      let horasLibres = horarioBarberia.filter((hora) => !ocupadasLimpio.includes(hora));
      
      // Obtener fecha actual en formato local (Año-Mes-Día) para comparar correctamente
      const anio = ahora.getFullYear();
      const mes = (ahora.getMonth() + 1).toString().padStart(2, "0");
      const dia = ahora.getDate().toString().padStart(2, "0");
      const hoyLocal = `${anio}-${mes}-${dia}`;
      
      if (fecha === hoyLocal) {
        horasLibres = horasLibres.filter(hora => hora > horaActualStr);
      }

      if (horasLibres.length === 0) {
        selectHora.innerHTML = '<option value="">Sin cupos disponibles</option>';
      } else {
        horasLibres.forEach((hora) => {
          const option = document.createElement("option");
          option.value = hora;
          option.textContent = formatear12h(hora);
          selectHora.appendChild(option);
        });
      }
    })
    .catch((err) => {
      console.error("Error:", err);
      mostrarMensaje("Error al consultar disponibilidad", "error");
    });
}

function cargarCitasCliente() {
  fetch("obtener_citas_cliente.php")
    .then((res) => res.json())
    .then((data) => {
      const tabla = document.getElementById("cuerpo-tabla-cliente");
      if (!tabla) return;
      tabla.innerHTML = "";
      if (data.length === 0) {
        tabla.innerHTML = `<tr><td colspan="6" style="text-align:center;">No tienes citas programadas aún.</td></tr>`;
        return;
      }
      data.forEach((cita) => {
        const estadoNorm = (cita.estado || "").trim().toLowerCase();
        tabla.innerHTML += `
        <tr>
            <td>${cita.servicio}</td>
            <td>${cita.barbero}</td>
            <td>${cita.fecha}</td>
            <td>${formatear12h(cita.hora)}</td>
            <td><span class="status-badge status-${estadoNorm}">${cita.estado}</span></td>
            <td class="acciones-celda">
                ${estadoNorm === "pendiente" ? `<button onclick="cancelarMiCita(${cita.id_cita})" class="btn-delete btn-accion"><i class="fas fa-times-circle" style="color:#ee5253; font-size:1.3rem;"></i></button>` : "---"}
            </td>
        </tr>`;
      });
    });
}

function cancelarMiCita(idCita) {
  if (!confirm("¿Estás seguro de que deseas cancelar esta cita?")) return;
  const datos = new FormData();
  datos.append("id_cita", idCita);
  fetch("cancelar_cita_cliente.php", { method: "POST", body: datos })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        mostrarMensaje("¡Cita cancelada!", "success");
        cargarCitasCliente();
        actualizarHorasDisponibles();
      } else {
        mostrarMensaje("Error: " + (data.error || "No se pudo cancelar"), "error");
      }
    });
}

// ==========================================
// 5. FUNCIONES PARA ADMIN (admin.php)
// ==========================================
let ultimaDataCitas = null;


function cargarTablaCitas(animar = true) {
  fetch("obtener_citas.php")
    .then((res) => res.json())
    .then((data) => {
      dibujarTabla(data, animar);
      const filterInput = document.getElementById("filter-date");
      if (filterInput) filterInput.value = "";
    });
}

function dibujarTabla(data, animar = false) {
  const cuerpo = document.getElementById("cuerpo-tabla");
  if (!cuerpo) return;
  cuerpo.innerHTML = "";
  const claseAnim = animar ? 'row-animation' : '';

  if (!data || data.length === 0) {
    cuerpo.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:20px; color:#666;"><i class="fas fa-info-circle"></i> No hay citas registradas.</td></tr>`;
    return;
  }

  data.forEach((cita) => {
    const estadoNorm = (cita.estado || "").trim().toLowerCase();
    cuerpo.innerHTML += `
            <tr class="${claseAnim}">
                <td>${cita.cliente}</td>
                <td>${cita.servicio}</td>
                <td>${cita.barbero}</td>
                <td>${cita.fecha}</td>
                <td>${formatear12h(cita.hora)}</td>
                <td><span class="status-badge status-${estadoNorm}">${cita.estado}</span></td>
                <td class="acciones-celda">
                    <button onclick="editarCita(${cita.id_cita}, '${cita.servicio}', '${cita.barbero}', '${cita.fecha}', '${cita.hora}')" class="btn-edit" title="Editar cita"><i class="fas fa-edit"></i></button>
                    <button onclick="eliminarCita(${cita.id_cita})" class="btn-delete" title="Borrar cita"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
  });
}

function filtrarPorFecha() {
  const fecha = document.getElementById("filter-date").value;
  if (!fecha) return;

  const cuerpo = document.getElementById("cuerpo-tabla");
  if (cuerpo) cuerpo.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:20px;"><i class="fas fa-spinner fa-spin"></i> Filtrando...</td></tr>`;

  ultimaDataCitas = null;
  fetch(`filtrar_citas.php?fecha=${fecha}`)
    .then((res) => res.json())
    .then((data) => {
      dibujarTabla(data, true);
    })
    .catch((err) => {
      console.error("Error:", err);
      mostrarMensaje("Error al filtrar citas", "error");
    });
}

function eliminarCita(id) {
  if (!confirm("¿Borrar esta cita?")) return;
  const d = new FormData(); d.append("id_cita", id);
  fetch("eliminar_cita.php", { method: "POST", body: d })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) { mostrarMensaje("Eliminada!"); cargarTablaCitas(); }
    });
}

function editarCita(id, servicio, barbero, fecha, hora) {
  const modal = document.getElementById("modal-editar");
  if (!modal) return;
  
  // Cargar catálogos antes de setear valores
  cargarCatalogosParaEdicion(() => {
    document.getElementById("edit-id").value = id;
    document.getElementById("edit-servicio").value = servicio;
    document.getElementById("edit-barbero").value = barbero;
    document.getElementById("edit-fecha").value = fecha;
    document.getElementById("edit-hora").value = hora;
    modal.style.display = "flex";
  });
}

function cargarCatalogosParaEdicion(callback) {
  fetch("obtener_catalogos.php")
    .then(res => res.json())
    .then(data => {
      const sServicio = document.getElementById("edit-servicio");
      const sBarbero = document.getElementById("edit-barbero");
      
      sServicio.innerHTML = "";
      const cats = { "Cabello": [], "Barba": [], "Especiales": [] };
      data.servicios.forEach(s => { if(s.activo == 1) cats[s.categoria].push(s); });
      
      for(let cat in cats) {
        if(cats[cat].length > 0) {
          const group = document.createElement("optgroup");
          group.label = cat;
          cats[cat].forEach(s => {
            const opt = document.createElement("option");
            opt.value = s.nombre;
            opt.textContent = s.nombre;
            group.appendChild(opt);
          });
          sServicio.appendChild(group);
        }
      }
      
      sBarbero.innerHTML = "";
      data.barberos.filter(b => b.activo == 1).forEach(b => {
        const opt = document.createElement("option");
        opt.value = b.nombre;
        opt.textContent = b.nombre;
        sBarbero.appendChild(opt);
      });
      
      if(callback) callback();
    });
}

function cerrarModal() {
  const modal = document.getElementById("modal-editar");
  if (modal) modal.style.display = "none";
}

// ==========================================
// 6. GESTIÓN DE USUARIOS (ADMIN)
// ==========================================
function cargarUsuarios() {
  fetch("obtener_usuarios.php")
    .then((res) => res.json())
    .then((data) => {
      const tabla = document.getElementById("cuerpo-tabla-usuarios");
      if (!tabla) return;
      tabla.innerHTML = "";
      data.forEach((u) => {
        const claseE = u.estado_usuario === "activo" ? "status-activo" : "status-inactivo";
        tabla.innerHTML += `
            <tr class="row-animation">
                <td><strong>${u.nombre || "Sin nombre"}</strong></td>
                <td>${u.email}</td>
                <td><span class="status-badge ${claseE}">${u.estado_usuario}</span></td>
                <td>
                    <div class="acciones-celda">
                        <button onclick="prepararEditarUsuario(${u.id_usuario}, '${u.nombre}', '${u.telefono || ""}')" class="btn-edit" title="Editar datos y contraseña"><i class="fas fa-key"></i></button>
                        <button onclick="eliminarUsuario(${u.id_usuario})" class="btn-delete" title="Borrar usuario"><i class="fas fa-user-slash"></i></button>
                    </div>
                </td>
            </tr>`;
      });
    });
}

function mostrarSeccion(seccion) {
  const sections = {
    citas: document.getElementById("seccion-citas"),
    usuarios: document.getElementById("seccion-usuarios"),
    barberos: document.getElementById("seccion-barberos"),
    servicios: document.getElementById("seccion-servicios")
  };

  for (let s in sections) {
    if (sections[s]) sections[s].style.display = (s === seccion) ? "block" : "none";
  }

  if (seccion === "citas") cargarTablaCitas();
  if (seccion === "usuarios") cargarUsuarios();
  if (seccion === "barberos") cargarTablaBarberos();
  if (seccion === "servicios") cargarTablaServicios();
}

// GESTIÓN DE BARBEROS (ADMIN)
function cargarTablaBarberos() {
  fetch("obtener_catalogos.php")
    .then(res => res.json())
    .then(data => {
      const cuerpo = document.getElementById("cuerpo-tabla-barberos");
      if (!cuerpo) return;
      cuerpo.innerHTML = "";
      data.barberos.forEach(b => {
        const statusClass = b.activo == 1 ? "status-activo" : "status-inactivo";
        const statusText = b.activo == 1 ? "Activo" : "Inactivo";
        cuerpo.innerHTML += `
          <tr class="row-animation">
            <td>${b.nombre}</td>
            <td><span class="status-badge ${statusClass}">${statusText}</span></td>
            <td>
              <div class="acciones-celda">
                <button onclick="editarGestion('barbero', ${b.id_barbero}, '${b.nombre}')" class="btn-edit" title="Editar nombre"><i class="fas fa-edit"></i></button>
                <button onclick="toggleEstadoGestion('barbero', ${b.id_barbero}, ${b.activo == 1 ? 0 : 1})" class="btn-delete" title="${b.activo == 1 ? 'Ocultar del cliente' : 'Mostrar al cliente'}">
                  <i class="fas ${b.activo == 1 ? 'fa-eye-slash' : 'fa-eye'}"></i>
                </button>
                <button onclick="eliminarGestion('barbero', ${b.id_barbero})" class="btn-delete" style="background:rgba(211,47,47,0.2); border-color:var(--rojo-barber);" title="Borrar">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>`;
      });
    });
}

// GESTIÓN DE SERVICIOS (ADMIN)
function cargarTablaServicios() {
  fetch("obtener_catalogos.php")
    .then(res => res.json())
    .then(data => {
      const cuerpo = document.getElementById("cuerpo-tabla-servicios");
      if (!cuerpo) return;
      cuerpo.innerHTML = "";
      data.servicios.forEach(s => {
        const statusClass = s.activo == 1 ? "status-activo" : "status-inactivo";
        const statusText = s.activo == 1 ? "Activo" : "Inactivo";
        cuerpo.innerHTML += `
          <tr class="row-animation">
            <td>${s.nombre}</td>
            <td>${s.categoria}</td>
            <td><span class="status-badge ${statusClass}">${statusText}</span></td>
            <td>
              <div class="acciones-celda">
                <button onclick="editarGestion('servicio', ${s.id_servicio}, '${s.nombre}', '${s.categoria}')" class="btn-edit" title="Editar servicio"><i class="fas fa-edit"></i></button>
                <button onclick="toggleEstadoGestion('servicio', ${s.id_servicio}, ${s.activo == 1 ? 0 : 1})" class="btn-delete" title="${s.activo == 1 ? 'Ocultar del cliente' : 'Mostrar al cliente'}">
                  <i class="fas ${s.activo == 1 ? 'fa-eye-slash' : 'fa-eye'}"></i>
                </button>
                <button onclick="eliminarGestion('servicio', ${s.id_servicio})" class="btn-delete" style="background:rgba(211,47,47,0.2); border-color:var(--rojo-barber);" title="Borrar">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>`;
      });
    });
}

function abrirModalGestion(tipo) {
  document.getElementById("gestion-titulo").textContent = tipo === 'barbero' ? 'Añadir Barbero' : 'Añadir Servicio';
  document.getElementById("gestion-tipo").value = tipo;
  document.getElementById("gestion-accion").value = 'agregar';
  document.getElementById("gestion-id").value = '';
  document.getElementById("gestion-nombre").value = '';
  document.getElementById("campo-categoria").style.display = tipo === 'servicio' ? 'block' : 'none';
  document.getElementById("modal-gestion").style.display = "flex";
}

function editarGestion(tipo, id, nombre, categoria = '') {
  document.getElementById("gestion-titulo").textContent = tipo === 'barbero' ? 'Editar Barbero' : 'Editar Servicio';
  document.getElementById("gestion-tipo").value = tipo;
  document.getElementById("gestion-accion").value = 'editar';
  document.getElementById("gestion-id").value = id;
  document.getElementById("gestion-nombre").value = nombre;
  if(tipo === 'servicio') {
    document.getElementById("gestion-categoria").value = categoria;
    document.getElementById("campo-categoria").style.display = 'block';
  } else {
    document.getElementById("campo-categoria").style.display = 'none';
  }
  document.getElementById("modal-gestion").style.display = "flex";
}

function cerrarModalGestion() {
  document.getElementById("modal-gestion").style.display = "none";
}

function toggleEstadoGestion(tipo, id, nuevoEstado) {
  const d = new FormData();
  d.append("accion", "estado");
  d.append("tipo", tipo);
  d.append("id", id);
  d.append("estado", nuevoEstado);

  fetch("guardar_gestion.php", { method: "POST", body: d })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        mostrarMensaje("Estado actualizado");
        tipo === 'barbero' ? cargarTablaBarberos() : cargarTablaServicios();
      }
    });
}

function eliminarGestion(tipo, id) {
  if (!confirm(`¿Estás seguro de eliminar este ${tipo} permanentemente?`)) return;

  const d = new FormData();
  d.append("accion", "eliminar");
  d.append("tipo", tipo);
  d.append("id", id);

  fetch("guardar_gestion.php", { method: "POST", body: d })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        mostrarMensaje("Eliminado correctamente");
        tipo === 'barbero' ? cargarTablaBarberos() : cargarTablaServicios();
      } else {
        mostrarMensaje("Error al eliminar", "error");
      }
    });
}

function prepararEditarUsuario(id, nombre, telefono) {
  document.getElementById("edit-user-id").value = id;
  document.getElementById("edit-user-nombre").value = nombre;
  document.getElementById("edit-user-telefono").value = telefono;
  document.getElementById("modal-editar-usuario").style.display = "flex";
}

function cerrarModalUser() {
  document.getElementById("modal-editar-usuario").style.display = "none";
}

function eliminarUsuario(id) {
  if (!confirm("¿Eliminar permanentemente este usuario?")) return;
  const d = new FormData(); d.append("id_usuario", id);
  fetch("eliminar_usuario.php", { method: "POST", body: d })
    .then((res) => res.json())
    .then((data) => { if (data.success) { mostrarMensaje("Eliminado!"); cargarUsuarios(); } });
}

// ==========================================
// 7. LISTENERS Y EVENTOS GLOBALES
// ==========================================
document.addEventListener("DOMContentLoaded", () => {
  const path = window.location.pathname;

  // RUTINA CLIENTES
  if (path.includes("clientes.php")) {
    // Cargar catálogos dinámicos
    cargarCatalogosCliente();

    cargarCitasCliente();
    const sb = document.getElementById("barbero");
    if (sb) sb.addEventListener("change", actualizarHorasDisponibles);

    const fp = document.getElementById("form-perfil");
    if (fp) {
      fp.addEventListener("submit", function(e) {
        e.preventDefault();
        fetch("actualizar_perfil_cliente.php", { method: "POST", body: new FormData(this) })
          .then((r) => r.json())
          .then((d) => { if (d.success) { mostrarMensaje("¡Actualizado!"); cerrarModalPerfil(); } });
      });
    }

    const formCita = document.getElementById("appointment-form");
    if (formCita) {
      formCita.addEventListener("submit", function(e) {
        const f = document.getElementById("fecha-cita").value;
        const h = document.getElementById("hora-cita").value;
        if (!f || !h) return;
        
        const fechaCita = new Date(`${f}T${h}:00`);
        const ahora = new Date();
        
        if (fechaCita < ahora) {
          e.preventDefault(); 
          mostrarMensaje("La fecha u hora elegida ya ha pasado", "error");
        }
      });
    }
  }

  // RUTINA ADMIN
  if (path.includes("admin.php")) {
    cargarTablaCitas();

    const fe = document.getElementById("form-editar");
    if (fe) {
      fe.addEventListener("submit", function(e) {
        e.preventDefault();
        fetch("actualizar_cita.php", { method: "POST", body: new FormData(this) })
          .then((r) => r.json())
          .then((d) => { if (d.status === "success") { mostrarMensaje("¡Actualizado!"); cargarTablaCitas(); cerrarModal(); } });
      });
    }

    const feu = document.getElementById("form-editar-usuario");
    if (feu) {
      feu.addEventListener("submit", function(e) {
        e.preventDefault();
        fetch("actualizar_usuario_admin.php", { method: "POST", body: new FormData(this) })
          .then((r) => r.json())
          .then((d) => { if (d.status === "success") { mostrarMensaje("Datos actualizados!"); cerrarModalUser(); } });
      });
    }

    const fg = document.getElementById("form-gestion");
    if (fg) {
      fg.addEventListener("submit", function(e) {
        e.preventDefault();
        const tipo = document.getElementById("gestion-tipo").value;
        const btn = this.querySelector('button[type="submit"]');
        if (btn) btn.disabled = true;

        fetch("guardar_gestion.php", { method: "POST", body: new FormData(this) })
          .then(async r => {
            const text = await r.text();
            try {
              return JSON.parse(text);
            } catch (err) {
              console.error("Error parsing JSON:", text);
              throw new Error("Respuesta inválida del servidor.");
            }
          })
          .then(d => {
            if (d.success) {
              mostrarMensaje("¡Guardado!");
              cerrarModalGestion();
              tipo === 'barbero' ? cargarTablaBarberos() : cargarTablaServicios();
            } else {
              mostrarMensaje(d.message || "Error al guardar", "error");
            }
          })
          .catch(err => {
            console.error(err);
            mostrarMensaje("Error de red o servidor: " + err.message, "error");
          })
          .finally(() => {
            if (btn) btn.disabled = false;
          });
      });
    }
  }
});

function cargarCatalogosCliente() {
  fetch("obtener_catalogos.php")
    .then(res => res.json())
    .then(data => {
      const sServicio = document.getElementById("servicio");
      const sBarbero = document.getElementById("barbero");
      if(!sServicio || !sBarbero) return;

      sServicio.innerHTML = '<option value="">-- Selecciona servicio --</option>';
      const cats = { "Cabello": [], "Barba": [], "Especiales": [] };
      data.servicios.forEach(s => { if(s.activo == 1) cats[s.categoria].push(s); });
      
      for(let cat in cats) {
        if(cats[cat].length > 0) {
          const group = document.createElement("optgroup");
          group.label = cat;
          cats[cat].forEach(s => {
            const opt = document.createElement("option");
            opt.value = s.nombre;
            opt.textContent = s.nombre;
            group.appendChild(opt);
          });
          sServicio.appendChild(group);
        }
      }
      
      sBarbero.innerHTML = '<option value="">-- Selecciona barbero --</option>';
      data.barberos.filter(b => b.activo == 1).forEach(b => {
        const opt = document.createElement("option");
        opt.value = b.nombre;
        opt.textContent = b.nombre;
        sBarbero.appendChild(opt);
      });
    });
}

// Cerrar sesión
function cerrarSesion() { window.location.href = "logout.php"; }

// Click fuera de modales
window.onclick = function(e) {
  const mP = document.getElementById("modal-perfil");
  const mE = document.getElementById("modal-editar");
  const mG = document.getElementById("modal-gestion");
  if (e.target == mP) mP.style.display = "none";
  if (e.target == mE) mE.style.display = "none";
  if (e.target == mG) mG.style.display = "none";
};

// Intervalo Admin (Refresco silencioso)
setInterval(() => {
  const seccionCitas = document.getElementById("seccion-citas");
  if (seccionCitas && seccionCitas.style.display !== "none" && !seccionCitas.classList.contains("seccion-oculta")) {
    const fi = document.getElementById("filter-date");
    // Solo si no hay filtro activo y no hay modal de edición abierto
    if (fi && fi.value === "") {
      const mod = document.getElementById("modal-editar");
      if (!mod || (mod.style.display !== "flex" && mod.style.display !== "block")) {
        cargarTablaCitas(false); // Refresco SILENCIOSO (sin ruidos/efectos)
      }
    }
  }
}, 10000);
