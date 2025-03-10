<style>
.bodycontant {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 70vh;
    background: url('images/home-banner1.jpg') center center/cover no-repeat; /* Use your background image here */
    color: white;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.content {
    position: relative;
    z-index: 2;
    max-width: 600px;
    padding: 20px;
    padding: 20px;
    background: #121e7e;
    border-radius: 10px;
}
.content h1 {
    font-size: 48px;
    font-weight: bold;
    margin-bottom: 20px;
}

/* Subtext Styling */
.content p {
    font-size: 18px;
    color: #fff;
    margin-bottom: 30px;
}

/* Button Styling */
.content .btn {
    display: inline-block;
    padding: 15px 30px;
    color: white;
    font-size: 16px;
    font-weight: bold;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    text-decoration: none;
}

.content .btn:hover {
    background-color: #121E7E;
}
</style>

<div class="container-jumbotron">
    <div class="bodycontant">
        <div class="content">
            <h1>Sorry!</h1>
            <p>No valid flight results found during revalidation. This may happen if the flight availability has changed. Please search again or choose an alternative flight.</p>
            <a href="flights" class="btn btn-typ7 ml-3 btn-primary">SEARCH AGAIN</a>
        </div>
    </div>
</div>