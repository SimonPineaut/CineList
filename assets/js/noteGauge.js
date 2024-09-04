function getNoteColor(note) {
    note > 100 ? 100 : note;
    note < 0 ? 0 : note;

    const red = '#DB2360';
    const yellow = '#D2D531';
    const green = '#21D07A';

    if (note >= 70) {
        color = green;
    } else if (note >= 50) {
        color = yellow;
    } else {
        color = red;
    }

    return color;

}
const gaugeElements = document.querySelectorAll('.gauge');

gaugeElements.forEach(gaugeElement => {
    const movieId = gaugeElement.dataset.resultId;
    const note = gaugeElement.dataset.resultNote;
    const noteCount = gaugeElement.dataset.resultNoteCount;
    const color = getNoteColor(note);

    const canvas = document.querySelector(`#gauge${movieId}`);
    const ctx = canvas.getContext('2d');
    const centerX = canvas.width / 2;
    const centerY = canvas.height / 2;
    const radius = Math.min(canvas.width, canvas.height) / 2 - 10;
    const lineWidth = 10;

    function drawGauge(percentage) {
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius - lineWidth / 2, 0, 2 * Math.PI);
        ctx.lineWidth = lineWidth;
        ctx.strokeStyle = '#222';
        ctx.stroke();
        
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius + lineWidth / 1.5, 0, 2 * Math.PI);
        ctx.lineWidth = lineWidth;
        ctx.strokeStyle = '#222';
        ctx.stroke();

        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
        ctx.lineWidth = lineWidth;
        ctx.strokeStyle = '#666';
        ctx.stroke();   

        const endAngle = (percentage / 100) * 2 * Math.PI;

        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, -Math.PI / 2, endAngle - Math.PI / 2, false);
        ctx.lineWidth = lineWidth;
        if (noteCount > 0) {
            ctx.strokeStyle = color;
        } else {
            ctx.strokeStyle = '#000';
        }
        ctx.stroke();

        ctx.font = '40px Arial';
        ctx.fillStyle = '#fff';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';

        if (noteCount > 0) {
            ctx.fillText(`${percentage}%`, centerX, centerY);
        } else {
            ctx.fillText('N/A', centerX, centerY);
        }
    }

    drawGauge(note);
})
