<?php

//    CHATGPT ADDED THE COMMENTS
//    ME TOO LAZY TO EXPLAIN STUFF

// Check if the 'url' parameter is set in the GET request
if (isset($_GET['url'])) {
    $url = $_GET['url']; // Retrieve the URL parameter from the GET request

    // Set the response headers
    // Uncomment the below header to restrict access to specific origins (for example, https://rndtrack.net)
    // header('Access-Control-Allow-Origin: https://rndtrack.net');
    header('Content-Type: application/json'); // Indicate that the response content type is JSON

    // Check if the 'url' parameter is not empty
    if (!empty($url)) {
        $apiUrl = "https://api.song.link/v1-alpha.1/links?url={$url}"; // API URL to fetch music streaming links from song.link/odesli

        try {
            $data = file_get_contents($apiUrl); // Fetch data from the API
            echo $data; // Output the API response directly to the client
        } catch (Exception $e) {
            http_response_code(500); // Handle any errors
            echo json_encode(['error' => 'Internal Server Error']);
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>dytukMedia's rndtrack</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <link rel="stylesheet" href="./style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/FortAwesome/Font-Awesome@6.x/css/all.min.css">
</head>

<body style="line-height: inherit; margin: 0; background-color: rgb(16 16 16 / 1); color: rgb(255 255 255 / 1);">
  <!-- Display a message if JavaScript is disabled in the user's browser -->
  <noscript>
    <div class="noscript-wrapper">
      <div class="noscript-content">
        <p class="noscript-message">Please enable JavaScript to use this website.</p>
        <p class="noscript-message">For safety, read our <a class="noscript-link" href="https://dytuk.media/privacy">Privacy Policy</a>.</p>
      </div>
    </div>
  </noscript>

  <script defer>
    // Fix for DarkReader browser extension, stops it breaking things
    document.onreadystatechange = function () {
      if (document.readyState === "complete") {
        const lock = document.createElement('meta');
        lock.name = 'darkreader-lock';
        document.head.appendChild(lock);
        console.log('Page fully loaded');
      }
    };

    // Fetching random tracks and returning them as JSON
    function fetchTracks() {
      <?php
        // Initialize a cURL session to fetch random tracks from the backend API
        $ch = curl_init('https://dytuk.media/rndtrack/api/random.php?multi');

        // Set options for cURL session
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return response as a string
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow any redirects
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Ignore SSL host verification

        // Add custom headers to the request
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: dytuk.media/rndtrack',
            'Accept: application/json',
        ]);

        // Execute the cURL request
        $response = curl_exec($ch);

        // Check for errors in the cURL request
        if (curl_errno($ch)) {
            echo "cURL error: " . curl_error($ch);
            curl_close($ch);
            exit;
        }

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Check the HTTP status code of the response
        curl_close($ch);

        if ($http_code === 403) {
            echo "Error: 403 Forbidden. The server is refusing to process the request."; // Handle 403 errors
            exit;
        }

        // Decode the JSON response
        $decodedIssues = json_decode($response, true);

        if ($decodedIssues === null) {
            // Handle invalid JSON responses
            echo "Error: Invalid JSON response.";
            echo "<pre>" . $response . "</pre>";
            exit;
        }

        // Process the data by removing unnecessary slashes from string values
        array_walk_recursive($decodedIssues, function (&$value) {
            $value = stripslashes($value);
        });

        // Encode the processed data back to JSON format
        $encodedIssues = json_encode($decodedIssues);
        if ($encodedIssues === false) {
            echo "Error: Failed to encode JSON.";
            exit;
        }

        echo "return " . $encodedIssues . ";"; // Output the final JSON response
      ?>
    }

    // Main function that initializes the app
    async function main() {
      const track = fetchTracks()[0]; // Get the first track from the fetched data
      let isPlaying = false; // Flag to check if audio is playing
      let currentlyPlayingAudio = null; // Reference to the currently playing audio

      // Function to create the main container for the app
      function createContainer() {
        const container = document.createElement('div');
        container.style.height = '100vh';
        container.style.display = 'flex';
        container.style.flexDirection = 'column';
        container.style.justifyContent = 'center';
        container.style.padding = '1.5rem';
        return container;
      }

      // Function to create the image container with track details
      function createImageContainer(albumArt, trackTitle, artist) {
        const container = document.createElement('div');
        container.classList.add('transition-all', 'ease-in', 'hover:-translate-y-1');
        container.style.position = 'relative';

        const image = document.createElement('img');
        image.id = 'img';
        image.src = albumArt; // Track album art
        image.alt = removeArtistsFromTitle(trackTitle, artist);
        image.classList.add('h-96');

        const playIcon = document.createElement('i');
        playIcon.classList.add('play-icon', 'fas', 'fa-circle-play', 'text-transparent', 'bg-clip-text', 'bg-gradient-to-r', 'from-pink-400', 'to-purple-500', 'font-semibold');

        const audioPlayer = document.createElement("audio");
        audioPlayer.src = track.previewLink; // Set the track preview link
        audioPlayer.volume = 0.2; // Default volume

        // Append elements to the container
        container.appendChild(image);
        container.appendChild(playIcon);
        container.appendChild(audioPlayer);
        return container;
      }

            // Function to create a div containing the artist and track title
            function createArtistTitleDiv(artist, trackTitle) {
        return createContentDiv(
          'main', 
          `<span class="text-transparent bg-clip-text bg-gradient-to-r from-pink-400 to-purple-500 font-semibold">${artist}</span>${removeArtistsFromTitle(trackTitle, artist)}`, 
          'text-center', 'text-2xl', 'text-lg', 'transition-all', 'ease-out', 
          'hover:-translate-y-1', 'hover:bg-[#202020]', 'max-w-sm', 'h-max', 
          'bg-grey', 'rounded-xl', 'p-3', 'flex', 'flex-col', 'bg-[#181818]'
        );
      }

      // Function to create the div that will hold music platform buttons
      function createMusicButtonsDiv() {
        const div = createContentDiv(
          'main', '', 
          'transition-all', 'ease-out', 'hover:-translate-y-1', 'hover:bg-[#202020]', 
          'max-w-sm', 'h-max', 'bg-grey', 'rounded-xl', 'p-3', 'flex', 'flex-col', 
          'bg-[#181818]'
        );
        const musicButtonsSpan = document.createElement('span');
        musicButtonsSpan.classList.add('text-center', 'text-sm', 'hide-sel');
        musicButtonsSpan.style.cursor = 'default';

        div.appendChild(musicButtonsSpan);
        return div;
      }

      // Load music buttons asynchronously to display links for various streaming platforms
      async function loadMusicButtons(musicButtonsDiv, link) {
        const musicButtonsSpan = musicButtonsDiv.querySelector('span');
        const musicButtons = await getLinks(link); // Fetch streaming links

        // Append each valid button to the container
        musicButtons.filter(Boolean).forEach(btn => musicButtonsSpan.appendChild(btn));
      }

      // Display a loading message while fetching links
      function showLoadingMessage(parent, message) {
        const loadingMessage = document.createElement('p');
        loadingMessage.innerHTML = message;
        loadingMessage.classList.add('text-center', 'text-sm', 'loading-message');
        parent.appendChild(loadingMessage);
      }

      // Event handler to toggle play/pause of audio tracks
      function createTogglePlayPauseHandler(audioPlayer, playIcon) {
        return function () {
          if (audioPlayer.paused || audioPlayer.ended) {
            // Pause any currently playing audio
            if (currentlyPlayingAudio && currentlyPlayingAudio !== audioPlayer) {
              currentlyPlayingAudio.pause();
              const previousPlayIcon = currentlyPlayingAudio.parentElement.querySelector(".play-icon");
              previousPlayIcon.classList.replace("fa-pause", "fa-play");
            }
            playIcon.classList.replace("fa-circle-play", "fa-circle-pause");
            audioPlayer.play();
            isPlaying = true;
            currentlyPlayingAudio = audioPlayer;
          } else {
            // Pause the current track
            playIcon.classList.replace("fa-circle-pause", "fa-circle-play");
            audioPlayer.pause();
            audioPlayer.currentTime = 0; // Reset playback position
            isPlaying = false;
            currentlyPlayingAudio = null;
          }
        };
      }

      // Set up event listeners for audio playback
      function setupAudioEventListeners() {
        const audioPlayers = document.getElementsByTagName("audio");
        Array.from(audioPlayers).forEach(audioPlayer => {
          audioPlayer.addEventListener("ended", function () {
            // Reset the play icon when audio ends
            const playIcon = this.parentElement.querySelector(".play-icon");
            playIcon.classList.replace("fa-circle-pause", "fa-circle-play");
            isPlaying = false;
            currentlyPlayingAudio = null;
          });
        });
      }

      // Prevent default behavior for media play/pause keyboard keys
      function preventMediaPlayPause() {
        document.addEventListener("keydown", event => {
          if (event.code === "MediaPlayPause") event.preventDefault();
        });
      }

      // Remove artist names from the track title for cleaner display
      function removeArtistsFromTitle(title, artists) {
        const escapeRegExp = string => string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); // Escape special regex characters
        artists.split(", ").forEach(artist => {
          if (!title.includes(`${artist} Remix`)) {
            const regex = new RegExp(`\\s?\\(with\\s${escapeRegExp(artist)}\\)(?=-)`, "gi");
            title = title.replace(regex, "").trim();
          }
        });
        return title;
      }

      // Fetch streaming links from odesli/song.link API
      async function getLinks(link) {
        const odesli = `${window.location.href}?url=${encodeURIComponent(link)}`; // Build CORS-bypassed URL
        const options = { method: "GET", headers: { "User-Agent": "dytuk.media/rndtrack" } };

        try {
          const response = await fetch(odesli, options);
          if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
          const odesRes = await response.json();

          const addedPlatforms = new Set(); // Track platforms to prevent duplicates
          return Object.values(odesRes.linksByPlatform || {}).map(platformInfo => {
            return createPlatformButton(platformInfo.url, addedPlatforms);
          }).filter(Boolean); // Remove null values
        } catch (error) {
          console.error("Error fetching links:", error.message);

          if (error.message === "NotSupportedError: The element has no supported sources.") {
            document.querySelector('.loading-message').innerText = 'No supported sources, sorry.';
          }

          return [];
        }
      }

      // Create a button for a music platform
      function createPlatformButton(url, addedPlatforms) {
        const platforms = {
          'https://music.amazon.com/': 'Amazon Music',
          'https://www.deezer.com/': 'Deezer',
          'https://geo.music.apple.com/': 'Apple Music',
          'https://open.spotify.com/': 'Spotify',
          'https://music.youtube.com/': 'YouTube Music',
          'https://listen.tidal.com': 'Tidal'
        };

        for (const [platformUrl, name] of Object.entries(platforms)) {
          if (url.includes(platformUrl) && !addedPlatforms.has(platformUrl)) {
            addedPlatforms.add(platformUrl); // Mark the platform as added
            const button = document.createElement('a');
            button.href = url;
            button.classList.add('music-button', name.toLowerCase().replace(' ', '-'));
            button.textContent = name;
            return button;
          }
        }

        document.querySelector('.loading-message').innerText = '';
        return null; // Return null if no matching platform found
      }

      // Create a reusable content div with specific text and classes
      function createContentDiv(id, text, ...classes) {
        const div = document.createElement('div');
        div.id = id;
        div.classList.add(...classes);
        div.innerHTML = text.replace(/\n/g, '<br>'); // Replace newlines with <br> for HTML
        return div;
      }

      // Create the copyright div displayed at the bottom of the app
      function createCopyrightDiv() {
        return createContentDiv(
          'main', 
          `Copyright &copy; ${new Date().getFullYear()} dytukMedia.<br /><i>dytukMedia is not a company.</i><br /><a class="noscript-link" href="https://dytuk.media/privacy">Privacy Policy</a>`, 
          'text-center', 'text-1xl', 'mx-auto', 'font-semibold'
        );
      }

      // Run the main logic
      const mainContainer = createContainer();
      const mainDiv = document.createElement('div');
      mainDiv.classList.add('main');
      const imageContainer = createImageContainer(track.albumArt, track.track, track.artist);

      // Add event listener to the play icon
      const playIcon = imageContainer.querySelector('.play-icon');
      const audioPlayer = imageContainer.querySelector('audio');
      playIcon.addEventListener("click", createTogglePlayPauseHandler(audioPlayer, playIcon));

      // Build and append other UI components
      const elementsContainer = document.createElement('div');
      elementsContainer.classList.add('space-y-4', 'my-auto');
      elementsContainer.appendChild(createArtistTitleDiv(track.artist, track.track));
      const musicButtonsDiv = createMusicButtonsDiv();

      elementsContainer.appendChild(musicButtonsDiv);
      elementsContainer.appendChild(createCopyrightDiv());

      mainDiv.appendChild(imageContainer);
      mainDiv.appendChild(elementsContainer);
      mainContainer.appendChild(mainDiv);
      document.body.appendChild(mainContainer);

      // Display loading message and load music buttons asynchronously
      showLoadingMessage(musicButtonsDiv, "Fetching streaming links...<br /><i>roughly 10 seconds</i>");
      loadMusicButtons(musicButtonsDiv, track.spotifyLink);

      // Set up event listeners and media key prevention
      setupAudioEventListeners();
      preventMediaPlayPause();
    }

    // Execute the main function to initialize the app
    main();
  </script>
