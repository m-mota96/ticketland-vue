import { ElNotification } from 'element-plus';

export const showNotification = (title, message, type) => {
    ElNotification({
        title,
        message,
        type,
    });
};