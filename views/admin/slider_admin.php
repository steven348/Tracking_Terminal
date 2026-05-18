<!doctype html>
<html lang="en">
  <head>
    <title>Title</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="">
  </head>
  <body>

    <style>

        body{
         margin:0;
         font-family:'Inter','Roboto',sans-serif;
         background:#071A2D;
         color:#ffffff;
       }

       /* SIDEBAR */
       .sidebar{
          width:250px;
          height:100vh;
          background:linear-gradient(135deg, #021121 0%, #0A2A44 100%);
          color:white;
          position:fixed;
          left:0;
          top:0;
          padding:20px;
          box-sizing:border-box;
          box-shadow:4px 0 20px rgba(0,0,0,0.35);
          border-right:1px solid rgba(255,255,255,0.05);
        }

        /* LOGO */
        .logo{
          font-size:28px;
          font-weight:800;
          margin-bottom:40px;
          text-align:center;
          letter-spacing:-0.5px;
          background:linear-gradient(135deg,#ffffff 20%,#00C2C7 90%);
          -webkit-background-clip:text;
          background-clip:text;
          color:transparent;
        }

        .logo span{
          color:#00C2C7;
        }

        /* TITULOS */
        .menu-title{
          font-size:12px;
          color:#7DA2BF;
          margin:25px 0 10px;
          text-transform:uppercase;
          letter-spacing:1px;
          font-weight:600;
        }

        /* ITEMS */
        .menu-item{
          display: block;
          width: calc(100% - 2px);
          padding:12px 15px;
          border-radius:14px;
          cursor:pointer;
          margin-bottom:14px;
          transition:0.3s ease;
          color:#E5F3FF;
          background:transparent;
          border:1px solid transparent;
          text-decoration:none;
        }

        /* HOVER */
        .menu-item:hover{
          background:rgba(255,255,255,0.08);
          transform:translateX(5px);
          border-color:rgba(0,194,199,0.25);
          box-shadow:0 4px 12px rgba(0,0,0,0.2);
        }

        /* ACTIVO */
        .menu-item.active{
          background:linear-gradient(135deg,#00C2C7,#0E5F8C);
          color:#ffffff;
          box-shadow:0 6px 18px rgba(0,194,199,0.35);
        }

        /* CONTENIDO */
        .content{
          margin-left:270px;
          padding:30px;
          min-height:100vh;
          background:#071A2D;
          color:#ffffff;
        }  



    </style>


    <aside class="sidebar animate__animated animate__fadeInLeft">
      <div class="logo">
        BUSITO <span>SV</span>
      </div>

      <a href="/TRACKING_TERMINAL/views/admin/vista_admin.php" class="menu-item">
        Dashboard
      </a>

      <a href="/TRACKING_TERMINAL/views/admin/trakins_admin.php" class="menu-item">
        Trackings
      </a>

      <a href="/TRACKING_TERMINAL/views/admin/usuarios_admin.php" class="menu-item">
        Usuarios
      </a>

      <div class="menu-item">
        Rutas
      </div>

      <div class="menu-item">
        Reportes
      </div>

    </aside>


    
    
    
    
      
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  </body>
</html>