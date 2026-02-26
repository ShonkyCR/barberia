const usuariosRegistrados = [
    { email: "admin@agencia.com", password: "123", rol: "admin" },
    { email: "cliente@correo.com", password: "abc", rol: "cliente" }
];

console.log("Script login.js cargado correctamente");

const loginForm = document.getElementById('login-form');

if (loginForm) {
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault(); 
        
        const emailIngresado = document.getElementById('email').value;
        const passwordIngresado = document.getElementById('password').value;

        console.log("Intentando entrar con:", emailIngresado);

        const usuarioEncontrado = usuariosRegistrados.find(u => 
            u.email === emailIngresado && u.password === passwordIngresado
        );

        if (usuarioEncontrado) {
            localStorage.setItem('userRole', usuarioEncontrado.rol);
            
            if (usuarioEncontrado.rol === "admin") {
                window.location.href = "admin.html"; 
            } else {
                window.location.href = "clientes.html";
            }
        } else {
            alert("Error al iniciar sesion, intentelo de nuevo.");
        }
    });
} else {
    
    console.error("No se encontró el formulario con ID 'login-form'");
}