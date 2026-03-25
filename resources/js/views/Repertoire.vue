<template>
  <AppLayout>
    <RepertoireList @play-audio="setAudio" />
    <StickyAudioPlayer :audio-url="currentAudioUrl" :track-name="currentTrackName" @close="clearAudio" />
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import AppLayout from '../components/AppLayout.vue';
import RepertoireList from '../components/RepertoireList.vue';
import StickyAudioPlayer from '../components/StickyAudioPlayer.vue';

const currentAudioUrl = ref('');
const currentTrackName = ref('');

function setAudio({ url, name }) {
  currentAudioUrl.value = url;
  currentTrackName.value = name;
}

function clearAudio() {
  currentAudioUrl.value = '';
  currentTrackName.value = '';
}
</script>
