<?php
    session_start();

    if(!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
    
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TCA Cosmic Design Replication</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500&family=Orbitron:wght@500&family=Tenor+Sans&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-color: #faf3f3;
            --accent-pink: #eecbc9; /* The shadow box color */
            --text-dark: #2c2c2c;
            --text-highlight: #b87e7c; /* The "natural" text color */
            --font-logo: 'Orbitron', sans-serif;
            --font-main: 'Montserrat', sans-serif;
            --font-headline: 'Tenor Sans', sans-serif;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--bg-color);
            font-family: var(--font-main);
            color: var(--text-dark);
            overflow-x: hidden; /* Prevent horizontal scroll due to absolute positioning nuances */
        }

        /* ================= HEADER ================= */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 50px;
        }

        .logo-container {
            display: flex;
            align-items: center;
            font-size: 1.5rem;
            font-weight: 500;
            letter-spacing: 1px;
        }

        .logo-tca {
            font-family: var(--font-logo);
            font-weight: 500;
            font-size: 1.8rem;
            letter-spacing: 2px;
            margin-right: 5px;
        }

        .logo-cosmic {
            color: #555;
            font-size: 1.1rem;
        }
        
        .logo-arrow {
             margin-left: 8px;
             font-size: 0.7rem;
             color: #555;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .search-bar-container {
            position: relative;
        }

        .search-bar {
            background-color: #f0e3e3;
            border: none;
            border-radius: 25px;
            padding: 10px 20px 10px 45px;
            width: 280px;
            outline: none;
            color: #777;
        }

        .search-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 1.1rem;
        }

        .nav-icon {
            font-size: 1.6rem;
            color: #887171;
            cursor: pointer;
        }


        /* ================= MAIN HERO SECTION ================= */
        .hero-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            min-height: 85vh;
            padding: 0 20px;
            position: relative;
        }

        /* === CENTER TEXT === */
        .hero-text-content {
            text-align: center;
            max-width: 600px;
            z-index: 10; 
        }

        .hero-logo-small {
            font-family: var(--font-logo);
            font-size: 1.2rem;
            margin-bottom: 10px;
            display: block;
            text-align: left;
            margin-left: 15%;
        }

        .headline {
            font-family: var(--font-headline);
            font-size: 3.5rem;
            font-weight: 400;
            line-height: 1.2;
            margin-bottom: 30px;
            color: #1a1a1a;
        }

        .highlight-natural {
            color: var(--text-highlight);
            font-size: 3.5rem;
        }

        .sub-text {
            font-size: 0.9rem;
            line-height: 1.6;
            color: #555;
            max-width: 80%;
            margin: 0 auto;
        }


        /* ================= IMAGE COLLAGE STYLES ================= */
        /* This is the complex part. We use absolute positioning within relative side containers. */

        .collage-container {
            position: relative;
            width: 25%; /* Occupy sides */
            height: 600px; /* Fixed height to manage absolute positions */
        }

        /* The Reusable Image Component Style */
        .collage-item {
            position: absolute;
            z-index: 2;
        }

        /* The pink shadow box */
        .img-box-shadow {
            background-color: var(--accent-pink);
            display: inline-block;
            /* The container determines the size based on the image inside */
        }

        /* The image itself, shifted up and left */
        .img-box-shadow img {
            display: block;
            transform: translate(-12px, -12px); /* Creates the offset effect */
            width: auto;
            height: auto;
            max-width: 130px; /* Base constraint, varied slightly below */
            box-shadow: 2px 2px 5px rgba(0,0,0,0.05); /* Subtle realism shadow */
        }

        /* Sizing variations based on the design */
        .size-s img { max-width: 100px; }
        .size-m img { max-width: 125px; }
        .size-l img { max-width: 150px; }
        .crop-tall img { max-height: 160px; object-fit: cover; }


        /* === LEFT SIDE POSITIONS === */
        .l-pos-1 { top: 20px; left: -60px; }
        .l-pos-2 { top: 30px; left: 77px; z-index: 3;}
        .l-pos-3 { top: 200px; left: 30px; }
        .l-pos-4 { top: 210px; left: 160px; z-index: 1;}
        .l-pos-5 { top: 400px; left: -30px; }
        .l-pos-6 { top: 390px; left: 110px; z-index: 3;}
        .l-pos-7 { top: 380px; left: 240px; }
        .l-pos-8 { top: 22px; left: 205px; z-index: 3;}

         /* === RIGHT SIDE POSITIONS === */
         .collage-right {
             /* Shift the whole container slightly right to match design gutter */
             transform: translateX(20px); 
         }
        .r-pos-1 { top: 20px; left: 0; z-index: 3;}
        .r-pos-2 { top: 0px; left: 140px; }
        .r-pos-3 { top: 20px; left: 270px; }
        .r-pos-4 { top: 200px; left: 50px; }
        .r-pos-5 { top: 180px; left: 190px; z-index: 3;}
        .r-pos-6 { top: 400px; left: 0px; z-index: 3;}
        .r-pos-7 { top: 380px; left: 130px; }
        .r-pos-8 { top: 400px; left: 260px; z-index: 3;}

        /* Responsive tweaks for smaller screens to prevent total breakage, 
           though exact replication breaks down on mobile */
           
        @media (max-width: 1200px) {
             .headline { font-size: 2.8rem; }
             .collage-container { transform: scale(0.8); }
        }
        @media (max-width: 900px) {
            .hero-section { flex-direction: column; height: auto;}
            .collage-container { width: 100%; height: 400px; margin-bottom: 50px; transform: scale(0.9); left: 5%;}
            .hero-text-content { order: -1; margin-bottom: 50px; margin-top: 50px;}
            .hero-logo-small { text-align: center; margin-left: 0;}
            header { padding: 20px; }
            .search-bar { width: 200px; }
        }
        .fade {
          opacity: 0;
         transition: opacity 0.6s ease-in-out;
        }
        .highlight { color: #432320; }
         .user-dropdown {
            position: absolute;
            top: 50px;
            right: 0;
            width: 180px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            padding: 10px 0;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all .3s ease;
            z-index: 999;
        }

        .user-dropdown.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .user-dropdown a {
            display: block;
            padding: 12px 20px;
            text-decoration: none;
            color: #432320;
            font-size: .9rem;
        }

        .user-dropdown a:hover {
            background: #f5e2e2;
        }


    </style>
</head>
<body>
    
    <header>
    <div class="logo-container">
        <span class="logo-tca">T<span class="highlight">C</span>A</span>
        <span class="logo-cosmic">Cosmic.</span>
        <i class="fa-solid fa-caret-right logo-arrow"></i>
    </div>

    <div class="nav-right">
        <div class="search-bar-container">
            <input type="text" class="search-bar">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
        </div>

        <a href="../cart.php">
    <i class="fa-solid fa-cart-shopping nav-icon"></i>
</a>


        <!-- USER ICON -->
        <i class="fa-regular fa-user nav-icon" id="userIcon"></i>

        <!-- USER NAVBAR -->
        <div class="user-dropdown" id="userDropdown">
            <a href="../profile.php">Profile</a>
            <a href="../orders.php">Orders</a>
            <a href="../product.php">Products</a>
            <a href="../logout.php?logout=true">Logout</a>
        </div>
    </div>
</header>

    <section class="hero-section">

        <div class="collage-container collage-left">
            <div class="collage-item l-pos-1 size-m">
                <div class="img-box-shadow"><img src="image_r16.jpg" alt="Beauty model"></div>
            </div>
            <div class="collage-item l-pos-2 size-m">
                <div class="img-box-shadow"><img src="image_r11.jpg" alt="Beauty model applying powder"></div>
            </div>
            <div class="collage-item l-pos-3 size-m crop-tall">
                <div class="img-box-shadow"><img src="image_r12.jpg" alt="Beauty model portrait"></div>
            </div>
             <div class="collage-item l-pos-4 size-l crop-tall">
                <div class="img-box-shadow"><img src="image_r13.jpg" alt="Beauty model close up"></div>
            </div>
             <div class="collage-item l-pos-5 size-m">
                <div class="img-box-shadow"><img src="image_r14.jpg" alt="Beauty model shadow"></div>
            </div>
            <div class="collage-item l-pos-6 size-m">
                <div class="img-box-shadow"><img src="image_r1.jpg" alt="Beauty model applying powder"></div>
            </div>
             <div class="collage-item l-pos-7 size-m">
                <div class="img-box-shadow"><img src="image_r15.jpg" alt="Beauty model"></div>
            </div>
             <div class="collage-item l-pos-8 size-m">
                <div class="img-box-shadow"><img src="image_r10.jpg" alt="Beauty model"></div>
            </div>
        </div>


        <div class="hero-text-content">
            <span class="hero-logo-small">T<span class="highlight">C</span>A</span>
            <h1 class="headline">Beauty that feels</h1>
        <span id="changing-word" class="highlight-natural">natural.</span>
            <p class="sub-text">We create gentle, modern beauty essentials made for all skin types. Our products focus on quality, confidence, and everyday radiance.</p>
        </div>


        <div class="collage-container collage-right">
             <div class="collage-item r-pos-1 size-m">
                <div class="img-box-shadow"><img src="image_r2.jpg" alt="Beauty model"></div>
            </div>
            <div class="collage-item r-pos-2 size-m">
                <div class="img-box-shadow"><img src="image_r3.jpg" alt="Beauty model applying powder"></div>
            </div>
            <div class="collage-item r-pos-3 size-m">
                <div class="img-box-shadow"><img src="image_r4.jpg" alt="Beauty model applying powder"></div>
            </div>
             <div class="collage-item r-pos-4 size-l crop-tall">
                <div class="img-box-shadow"><img src="image_r5.jpg" alt="Beauty model"></div>
            </div>
            <div class="collage-item r-pos-5 size-l crop-tall">
                <div class="img-box-shadow"><img src="image_r6.jpg" alt="Beauty model"></div>
            </div>
             <div class="collage-item r-pos-6 size-m">
                <div class="img-box-shadow"><img src="image_r7.jpg" alt="Beauty model"></div>
            </div>
            <div class="collage-item r-pos-7 size-m">
                <div class="img-box-shadow"><img src="image_r8.jpg" alt="Beauty model applying powder"></div>
            </div>
            <div class="collage-item r-pos-8 size-m">
                <div class="img-box-shadow"><img src="image_r9.jpg" alt="Beauty model applying powder"></div>
            </div>
        </div>

    </section>


<script>
    const words = ["natural.", "confidence.", "like you."]; 
    let index = 0;

    const textSpan = document.getElementById("changing-word");

    setInterval(() => {
        // fade out
        textSpan.classList.add("fade");
        
        setTimeout(() => {
            // change word
            index = (index + 1) % words.length;
            textSpan.textContent = words[index];

            // fade in
            textSpan.classList.remove("fade");
        }, 600);

    }, 3000); // every 3 seconds

        // USER NAVBAR TOGGLE
    const userIcon = document.getElementById("userIcon");
    const userDropdown = document.getElementById("userDropdown");

    userIcon.addEventListener("click", (e) => {
        e.stopPropagation();
        userDropdown.classList.toggle("active");
    });

    document.addEventListener("click", () => {
        userDropdown.classList.remove("active");
    });
</script>


</body>
</html>