window.onload = function () {
    const canvas = document.getElementById("stars");
    const ctx = canvas.getContext("2d");

    let stars = [];
    const numStars = 100;

    function resizeCanvas() {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    }

    window.addEventListener("resize", resizeCanvas);
    resizeCanvas();

    function initStars() {
        for (let i = 0; i < numStars; i++) {
            stars.push({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height,
                radius: Math.random() * 1.5 + 0.5,
                speed: Math.random() * 0.5 + 0.2,
                alpha: Math.random(),
            });
        }
    }

    function drawStars() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = "white";
        for (let star of stars) {
            ctx.globalAlpha = star.alpha;
            ctx.beginPath();
            ctx.arc(star.x, star.y, star.radius, 0, 2 * Math.PI);
            ctx.fill();
        }
        ctx.globalAlpha = 1;
    }

    function animate() {
        for (let star of stars) {
            star.y += star.speed;
            if (star.y > canvas.height) {
                star.y = 0;
                star.x = Math.random() * canvas.width;
            }
        }
        drawStars();
        requestAnimationFrame(animate);
    }

    initStars();
    animate();
};