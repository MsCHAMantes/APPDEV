<?php
require './config/db.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css2?family=Advent+Pro:wght@400;700&family=Iceland&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Homepage</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: none;
            margin: 0;
            padding: 0;
        }
        .navbar {
            font-family: 'Iceland', Arial, sans-serif;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 35px;
            position: relative; 
            z-index: 9999;      
            background-color: none;
        }

        .navbar h1 {
            font-family: 'Iceland', Arial, sans-serif;
            color: black;
            margin: 0;
            font-size: 33px;
        }

        .nav-links {
            display: flex;
            gap: 15px;
        }

        .nav-links a {
            font-size: 25px;
            text-decoration: none;
            color: black;
            padding: 8px 16px;
            border-radius: 5px;
        }

        .nav-links a:hover {
            color: white;
            background-color: #995D64;
        }

        .content {
            position: relative;
            text-align: center;
            color: #000;
            padding-top: 15px;
            z-index: 1; 
        }

        h2 {
            font-family: 'Iceland', Arial, sans-serif;
            font-size: 100px;
            position: absolute;
            top: -70px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 2;
            color: rgba(0, 0, 0, 0.8);
        }

        .image1 {
            width: 800px;
            height: 760px;
            object-fit: contain;
            position: relative;
            z-index: 1;
            display: block;
            margin: -60px auto 0;
        }

        .overlay-image {
            position: absolute;
            top: 100px; 
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 1s ease-in-out;
            z-index: 1; 
        }

        .image2 { width: 320px; height: auto; top: 80px; left: 50%; }
        .image3 { width: 600px; height: 490px; top: 150px; left: 52%; }
        .image4 { width: 540px; height: 440px; top: 170px; left: 53%; }

        .overlay-image.active { opacity: 1; }

        .highlight { color: #432320; }

        .left-text {
            font-family: 'Advent pro', Arial, sans-serif;
            position: absolute;
            left: 3%;
            top: 55%;
            text-align: left;
            color: #000;
            font-size: 40px;
            line-height: 1.5;
            z-index: 1;
        }

        .left-text small {
            font-family: 'Advent pro', Arial, sans-serif;
            font-size: 20px;
            display: block;
            margin: 0;
        }

        .right-text {
            font-family: 'Advent pro', Arial, sans-serif;
            position: absolute;
            right: 3%;
            top: 80%;
            text-align: right;
            color: #000;
            font-size: 28px;
            z-index: 1;
        }

        html { scroll-behavior: smooth; }

        .about {
            font-family: 'Advent Pro', Arial, sans-serif;
            color: black;
            height: 100vh;
            padding-top: 63px;
            text-align: left;
            background: url('bgforabout.png') no-repeat center center;
            background-size: cover;
            background-attachment: scroll; 
        }

        .abouttext {font-family: 'Advent pro', Arial, sans-serif; font-size: 32px; margin-top: 150px; }
        .text { font-size: 23px; text-align: center; margin: 4%; }
        .text .line { margin: 5px 0; }

        .main{ background: url(background.png) no-repeat center center; background-size: cover; }
        .best{ background: url(background.png) no-repeat center center; background-size: cover; }

        .box {
            width: 1127px;
            height: 48px;
            background-color: #F0DDD6; 
            margin-left: 140px;
            margin-top: 48px;
        }
        .box2{
             width: 1127px;
            height: 48px;
            background-color: #F0DDD6; 
            margin-right: 140px;
            margin-top: 48px;           
        }
        .title{
        font-family: 'Advent pro', Arial, sans-serif;
        position: absolute;
        left: 13%;
        text-align: left;
        color: #000;
        font-size: 50px;
        margin-top: -75px;
        }
        .scroll-container {
    position: relative;
    width: 100%;
    margin-top: 40px;
}

.image-scroller {
    display: flex;
    overflow-x: auto;
    gap: 20px;
    padding: 20px;
    scroll-behavior: smooth;
    scrollbar-width: none; 
}

.image-scroller::-webkit-scrollbar {
    display: none; 
}

.image-scroller img {
    width: 220px;
    height: 260px;
    object-fit: cover;
    border-radius: 10px;
}

.scroll-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: #995D64;
    color: white;
    border: none;
    padding: 12px 18px;
    font-size: 25px;
    border-radius: 50%;
    cursor: pointer;
    z-index: 10;
    opacity: 0.8;
}

.scroll-btn:hover {
    opacity: 1;
}

.scroll-btn.left {
    left: 10px;
}

.scroll-btn.right {
    right: 10px;
}
.models{
    width: 350px ;
    height: 330px;
    

}
.models2{
    width: 370px ;
    height: 390px;
    

}

    </style>
</head>

<body>

<div class="main">
    <div class="navbar">
        <h1>T <span class="highlight">C</span> A</h1>
        <div class="nav-links">
            <a href="#about">About</a>
            <a href="#contact">Contact</a>
            <a href="login.php">Buy Now</a>
        </div>
    </div>

    <div class="content">
        <h2>T<span class="highlight">C</span>A COSMETICS</h2>
        <img src="image1.png" class="image1">
    </div>

    <div class="left-text">
        <p>Timeless.<br>Classy. Alluring.</p>
        <small>The Beauty That Captures All — TCA Cosmetics.</small>
    </div>
    
    <div class="right-text">
        <p>Be iconic. Be you.</p>
    </div>

    <img src="image2.png" class="overlay-image image2 active">
    <img src="image3.png" class="overlay-image image3">
    <img src="image4.png" class="overlay-image image4">
</div>

<section id="about" style="position: relative;">
    <img src="design.png" style="position: absolute; top: -120px; left: 850px; width: 400px; height: 400px; opacity: 50%;">
    <div style="display: flex; align-items: center; margin: 50px 0;">
        <img src="model1.png" class="models" style="margin-right: 10px; margin-left: 50px; border-radius: 100px; z-index: 2;">
        <div style="text-align: left; margin-right: 40px; z-index: 1;">
            <p class="abouttext">
                At TCA Cosmetics, we believe that beauty starts with confidence. Founded with a passion for self-expression and timeless elegance, we create high-quality cosmetics designed to enhance your natural glow — not hide it.
            </p>
            <hr style="border: 1px solid #995D64; width: 100%; margin-top: 10px;">
        </div>
    </div>

    <div style="display: flex; align-items: center; margin: 50px 0; flex-direction: row-reverse;">
        <img src="model2.png" class="models2" style="margin-left: 10px; margin-right: 50px; border-radius: 100px;">
        <div style="text-align: right; margin-left: 40px;">
            <p class="abouttext">
                Inspired by the beauty of simplicity, our collections focus on nude and matte tones that celebrate every skin tone and personality. Each product is carefully formulated to bring out the best version of you — bold, radiant, and authentically beautiful.
            </p>
            <hr style="border: 1px solid #995D64; width: 100%; margin-top: 10px;">
        </div>
    </div>

    <div style="display: flex; align-items: center; margin: 50px 0;">
        <img src="model3.png" class="models" style="margin-right: 10px; margin-left: 50px; border-radius: 100px; z-index: 2;">
        <div style="text-align: left; margin-right: 40px; z-index: 1;" >
            <p class="abouttext">
                We stand for Trust, Confidence, and Allure — the essence behind TCA. From our packaging to our pigments, every detail reflects our commitment to quality, inclusivity, and innovation in modern beauty.
            </p>
            <hr style="border: 1px solid #995D64; width: 100%; margin-top: 10px;">
        </div>
    </div>
      <img src="powder.png" style="position: absolute; top: 700px; margin-left: -10; width: 500px; height: 200x; opacity: 50%;">

</section>

<section id="contact" style="background-color: #cba2a3; padding: 40px 5%;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <div style="font-family: 'Orbitron', sans-serif; font-size: 2.85rem; font-weight: 600;">T<span class="highlight">C</span>A</div>
            <p style="font-family: 'Poppins', sans-serif; font-size: 0.85rem; color: #1a1a1a;">
                Here's our company's social media where you can reach us:
            </p>
            <div style="display: flex; gap: 15px; font-size: 2.4rem; margin-top: -10px;">
                <a href="https://facebook.com" target="_blank" style="color: #432320;"><i class="fa-brands fa-facebook-square"></i></a>
                <a href="https://instagram.com" target="_blank" style="color: #432320;"><i class="fa-brands fa-instagram"></i></a>
                <a href="https://x.com" target="_blank" style="color: #432320;"><i class="fa-solid fa-x"></i></a>
            </div>
        </div>
        <div>
            <p style="font-size: 0.75rem; position: absolute; margin-top: 70px;">You can also reach us here. We look forward to assisting you.</p>
            <div style="margin-top: 100px; background-color: #eeddd8; padding: 15px 25px; border-radius: 8px; min-width: 280px; font-family: 'Poppins', sans-serif; font-size: 0.85rem;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
                <i class="fa-solid fa-phone"></i>
                <span>(032) 789-2048</span>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-envelope"></i>
                <span>tcacompanygroup@gmail.com</span>
            </div>
        </div>
        </div>

    </div>
</section>


<script>
    const images = document.querySelectorAll('.overlay-image');
    let index = 0;

    setInterval(() => {
        images[index].classList.remove('active');
        index = (index + 1) % images.length;
        images[index].classList.add('active');
    }, 3000);
    if (window.location.hash) {
        history.replaceState(null, null, window.location.pathname);
    }
    const sections = document.querySelectorAll('section');
    const options = {
        root: null,
        rootMargin: '0px',
        threshold: 0.5 
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const id = entry.target.id;
                if (id) {
                    history.replaceState(null, null, `#${id}`);
                } else {
                    history.replaceState(null, null, window.location.pathname);
                }
            }
        });
    }, options);

    sections.forEach(section => observer.observe(section));


  function scrollLeft() {
    document.getElementById("imageScroller").scrollBy({
        left: -200,
        behavior: "smooth"
    });
}

function scrollRight() {
    document.getElementById("imageScroller").scrollBy({
        left: 200,
        behavior: "smooth"
    });
}  

const reviewSlides = document.querySelectorAll('.review-slide');
let reviewIndex = 0;

function showSlide(index){
    reviewSlides.forEach((slide,i)=>{
        slide.style.opacity = i === index ? '1' : '0';
    });
}

let reviewTimer = setInterval(()=>{
    reviewIndex = (reviewIndex + 1) % reviewSlides.length;
    showSlide(reviewIndex);
}, 3000);

document.getElementById('prevReview').addEventListener('click', ()=>{
    reviewIndex = (reviewIndex - 1 + reviewSlides.length) % reviewSlides.length;
    showSlide(reviewIndex);
    resetTimer();
});

document.getElementById('nextReview').addEventListener('click', ()=>{
    reviewIndex = (reviewIndex + 1) % reviewSlides.length;
    showSlide(reviewIndex);
    resetTimer();
});

function resetTimer(){
    clearInterval(reviewTimer);
    reviewTimer = setInterval(()=>{
        reviewIndex = (reviewIndex + 1) % reviewSlides.length;
        showSlide(reviewIndex);
    }, 3000);
}

</script>

</body>
</html>


