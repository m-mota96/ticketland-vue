<script setup>
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
</script>

<template>
    <el-menu
        class="el-menu-demo"
        mode="horizontal"
        :ellipsis="false"
    >
        <el-menu-item index="0">
        
        </el-menu-item>
        <!-- <el-menu-item index="1">Processing Center</el-menu-item> -->
        <el-sub-menu index="2">
            <template #title>{{ $page.props.auth.user.name }}</template>
            <el-menu-item index="2-1">
                <ResponsiveNavLink
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="has-text-dark"
                >
                    <font-awesome-icon :icon="['fas', 'arrow-right-from-bracket']" class="mr-2" />
                    Cerrar sesión
                </ResponsiveNavLink>
            </el-menu-item>
        </el-sub-menu>
    </el-menu>
</template>

<style scoped>
.el-menu--horizontal > .el-menu-item:nth-child(1) {
    margin-right: auto;
}
</style>