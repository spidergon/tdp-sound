import Player from './player'

function animateSoundBtn (btn, icon) {
  btn.classList.remove('loading', 'speaker', 'speaker-mute')
  btn.classList.add(icon)
}

function run (link, player, playlist) {
  player.src = playlist[link.index()].file
  link
    .addClass('active')
    .siblings()
    .removeClass('active')
  player.load()
  player.play()
}

/* eslint-disable no-extra-semi */
;(function ($) {
  const rawTdpOptions = document.querySelector('.sound-options')
  if (rawTdpOptions && rawTdpOptions.value) {
    const htmlPlaylist = $('#playlist')
    const audio = $('.playlist-container audio')
    // Load the sounds
    const tdpOptions = JSON.parse(rawTdpOptions.value)
    rawTdpOptions.parentNode.removeChild(rawTdpOptions) // Remove the DOM element containing the sounds options

    const playlist = []
    Object.keys(tdpOptions).forEach((title, index) => {
      const file = tdpOptions[title]
      if (file) {
        title = file.replace(/^.*[\\/]/, '').replace(/_/g, ' ').replace(/\..*$/, '') // get the real title
        playlist.push({
          title,
          file,
          howl: null
        })
        const active = index === 0 ? 'class="active"' : ''
        if (htmlPlaylist) {
          htmlPlaylist.append(`<span ${active || ''}>${title}</span>`)
        }
      }
    })
    // TODO July 13, 2019: Remove these!
    htmlPlaylist.append(`<span>Beethoven-MoonlightSonata</span>`)
    playlist[3] = { file: 'http://www.archive.org/download/MoonlightSonata_755/Beethoven-MoonlightSonata.mp3' }
    htmlPlaylist.append(`<span>CanoninD</span>`)
    playlist[4] = { file: 'http://www.archive.org/download/CanonInD_261/CanoninD.mp3' }
    // htmlPlaylist.append(
    //   `<a href="http://www.archive.org/download/bolero_69/Bolero.mp3">Bolero</a>`
    // )
    // htmlPlaylist.append(
    //   `<a href="http://www.archive.org/download/MoonlightSonata_755/Beethoven-MoonlightSonata.mp3">Beethoven-MoonlightSonata</a>`
    // )
    // htmlPlaylist.append(
    //   `<a href="http://www.archive.org/download/CanonInD_261/CanoninD.mp3">CanoninD</a>`
    // )
    // htmlPlaylist.append(
    //   `<a href="http://www.archive.org/download/PatrikbkarlChamberSymph/PatrikbkarlChamberSymph_vbr_mp3.zip">PatrikbkarlChamberSymph_vbr</a>`
    // )

    // Init sound button effect
    const soundBtn = document.getElementById('sound-btn')
    if (playlist.length > 0 && soundBtn) {
      if (htmlPlaylist.length > 0) {
        const player = audio[0]
        soundBtn.onclick = () => {
          if (player.paused) player.play()
          else player.pause()
        }
      } else {
        const player = new Player(playlist, icon =>
          animateSoundBtn(soundBtn, icon)
        )
        soundBtn.onclick = () => {
          if (!player.playing()) player.play()
          else player.pause()
        }
      }
    }

    // Init HTML playlist effect
    if (audio.length > 0) {
      let current = 0
      const tracks = htmlPlaylist.find('span')
      const len = tracks.length

      if (len > 0) {
        audio[0].src = playlist[0].file // Set the first track

        tracks.click(e => {
          e.preventDefault()
          const link = $(e.target)
          current = link.index()
          run(link, audio[0], playlist)
        })

        audio[0].addEventListener('ended', e => {
          current++
          let link
          if (current === len) {
            current = 0
            link = tracks[0]
          } else {
            link = tracks[current]
          }
          run($(link), audio[0], playlist)
        })

        audio[0].addEventListener('playing', e => {
          animateSoundBtn(soundBtn, 'speaker')
        })

        audio[0].addEventListener('pause', e => {
          animateSoundBtn(soundBtn, 'speaker-mute')
        })
      }
    }
  }
})(jQuery)
