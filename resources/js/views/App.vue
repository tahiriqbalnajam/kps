<template>
  <router-view/>
</template>

<script>
import { userStore } from '@/store/user';
import request from '@/utils/request';
import { watch } from 'vue';

export default {
  name: 'App',
  setup() {
    const store = userStore();

    // Helper to sync player ID
    const syncPlayerId = async (playerId) => {
      if (store.id && playerId) {
        try {
          await request({
            url: '/api/v1/update-device-token',
            method: 'post',
            data: {
              user_id: store.id,
              player_id: playerId
            }
          });
          console.log('OneSignal Player ID synced');
        } catch (error) {
          console.error('Failed to sync OneSignal Player ID', error);
        }
      }
    };

    // Watch for user login
    watch(() => store.id, async (newId) => {
      if (newId && window.OneSignalDeferred) {
         window.OneSignalDeferred.push(async (OneSignal) => {
             const playerId = await OneSignal.User.PushSubscription.id;
             if (playerId) {
                 syncPlayerId(playerId);
             }
         });
      }
    });
  },
  mounted() {
    // Check initial state
    window.OneSignalDeferred = window.OneSignalDeferred || [];
    window.OneSignalDeferred.push(async (OneSignal) => {
        // Prompt user
        OneSignal.Slidedown.promptPush();

        // Check if already subscribed
        const playerId = await OneSignal.User.PushSubscription.id;
        const store = userStore();
        if (playerId && store.id) {
            this.syncPlayerId(playerId);
        }

        // Listen for changes
        OneSignal.User.PushSubscription.addEventListener("change", async (event) => {
            if (event.current.id) {
                 const currentStore = userStore();
                 if (currentStore.id) {
                     // We need to call the sync function. 
                     // Since setup() variables aren't automatically available in mounted unless returned,
                     // I should probably move logic to methods or use setup entirely.
                     // But for simplicity, I'll inline the request here or refactor to Options API + setup mix.
                     try {
                        await request({
                            url: '/api/v1/update-device-token',
                            method: 'post',
                            data: {
                            user_id: currentStore.id,
                            player_id: event.current.id
                            }
                        });
                     } catch(e) {}
                 }
            }
        });
    });
  },
  methods: {
      async syncPlayerId(playerId) {
          const store = userStore();
          if (store.id && playerId) {
            try {
              await request({
                url: '/api/v1/update-device-token',
                method: 'post',
                data: {
                  user_id: store.id,
                  player_id: playerId
                }
              });
            } catch (error) {
              console.error(error);
            }
          }
      }
  }
};
</script>
