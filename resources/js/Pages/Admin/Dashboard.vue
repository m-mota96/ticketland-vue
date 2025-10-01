<script setup>
import Menu from './Menu.vue';
import Navbar from './Navbar.vue';

</script>

<template>
    <div class="container-fluid">
        <Menu :index="'2'" :countDocs="$page.props.countDocs"></Menu>
        <div style="width: 85%;">
            <Navbar></Navbar>
            <el-row :gutter="20" class="p-6">
                <el-col :span="12">
                    <el-row :gutter="30">
                        <el-col :span="12" class="mb-5">
                            <el-card class="p-6"></el-card>
                        </el-col>
                        <el-col :span="12" class="mb-5">
                            <el-card class="p-6"></el-card>
                        </el-col>
                        <el-col :span="24" class="mb-5">
                            <el-card class="p-6"></el-card>
                        </el-col>
                    </el-row>
                </el-col>
                <el-col :span="12">
                    <el-card class="p-6" style="height: 33vh;"></el-card>
                </el-col>
            </el-row>
        </div>
    </div>
</template>

<style>
.container-fluid {
    display: flex;
}
</style>