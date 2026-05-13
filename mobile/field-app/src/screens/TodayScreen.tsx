import React, { useState, useEffect, useCallback } from 'react';
import {
  View,
  Text,
  FlatList,
  StyleSheet,
  RefreshControl,
  Alert,
  Modal,
  TextInput,
  ScrollView,
  Image,
  TouchableOpacity,
} from 'react-native';
import * as ImagePicker from 'expo-image-picker';
import { authService, offlineQueueService, workTaskService } from '../services';
import { Button } from '../components/Button';
import { TaskCard } from '../components/TaskCard';
import type { WorkTask, QueuedLog } from '../types';

interface Props {
  onLogout: () => void;
}

const taskTitle = (task: WorkTask) => task.title || `Việc #${task.id}`;

export function TodayScreen({ onLogout }: Props) {
  const [tasks, setTasks] = useState<WorkTask[]>([]);
  const [pendingLogs, setPendingLogs] = useState<QueuedLog[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [selectedTask, setSelectedTask] = useState<WorkTask | null>(null);
  const [showLogModal, setShowLogModal] = useState(false);
  const [notes, setNotes] = useState('');
  const [photoUris, setPhotoUris] = useState<string[]>([]);
  const [isSubmitting, setIsSubmitting] = useState(false);

  const loadTasks = useCallback(async () => {
    try {
      const todayTasks = await workTaskService.getTodayTasks();
      setTasks(todayTasks);
    } catch (err) {
      Alert.alert('Lỗi', err instanceof Error ? err.message : 'Không thể tải việc hôm nay');
    }
  }, []);

  const loadPendingLogs = useCallback(async () => {
    const queue = await offlineQueueService.getQueue();
    setPendingLogs(queue.filter(q => q.syncStatus !== 'synced'));
  }, []);

  const initialize = useCallback(async () => {
    setIsLoading(true);
    await Promise.all([loadTasks(), loadPendingLogs()]);
    setIsLoading(false);
  }, [loadTasks, loadPendingLogs]);

  useEffect(() => {
    initialize();
  }, [initialize]);

  const handleRefresh = async () => {
    setIsRefreshing(true);
    await initialize();
    setIsRefreshing(false);
  };

  const handleTaskPress = (task: WorkTask) => {
    if (task.status === 'done' || task.status === 'cancelled') return;
    setSelectedTask(task);
    setShowLogModal(true);
  };

  const handleAcceptTask = async (task: WorkTask) => {
    try {
      const updated = await workTaskService.acceptTask(task.id);
      setTasks(prev => prev.map(t => (t.id === task.id ? updated : t)));
    } catch (err) {
      Alert.alert('Lỗi', err instanceof Error ? err.message : 'Không thể nhận việc');
    }
  };

  const handleStartTask = async (task: WorkTask) => {
    try {
      const updated = await workTaskService.startTask(task.id);
      setTasks(prev => prev.map(t => (t.id === task.id ? updated : t)));
    } catch (err) {
      Alert.alert('Lỗi', err instanceof Error ? err.message : 'Không thể bắt đầu');
    }
  };

  const handlePickPhoto = async () => {
    const permission = await ImagePicker.requestCameraPermissionsAsync();
    if (!permission.granted) {
      Alert.alert('Lỗi', 'Cần quyền truy cập camera để chụp ảnh');
      return;
    }

    const result = await ImagePicker.launchCameraAsync({
      mediaTypes: ['images'],
      quality: 0.8,
    });

    if (!result.canceled && result.assets[0]) {
      setPhotoUris(prev => [...prev, result.assets[0].uri]);
    }
  };

  const handleRemovePhoto = (index: number) => {
    setPhotoUris(prev => prev.filter((_, i) => i !== index));
  };

  const handleSubmitLog = async () => {
    if (!selectedTask) return;

    setIsSubmitting(true);

    const clientUuid = `${Date.now()}-${Math.random().toString(36).slice(2)}`;

    await offlineQueueService.enqueue({
      localId: clientUuid,
      taskId: selectedTask.id,
      notes: notes.trim() || null,
      photoUris,
      loggedAt: new Date().toISOString(),
      actualStartAt: null,
      actualEndAt: null,
      metadata: {},
    });

    const result = await offlineQueueService.retry(clientUuid);
    await loadPendingLogs();

    if (result.success) {
      setTasks(prev => prev.map(t => (t.id === selectedTask.id ? { ...t, status: 'done' } : t)));
    }

    setNotes('');
    setPhotoUris([]);
    setShowLogModal(false);
    setSelectedTask(null);
    setIsSubmitting(false);

    Alert.alert(
      result.success ? 'Thành công' : 'Đã lưu chờ đồng bộ',
      result.success ? 'Đã gửi nhật ký đồng áng' : 'Nhật ký vẫn nằm trong hàng đợi và có thể thử lại'
    );
  };

  const handleRetryLog = async (log: QueuedLog) => {
    const result = await offlineQueueService.retry(log.localId);
    if (result.success) {
      await loadPendingLogs();
      Alert.alert('Thành công', 'Đã đồng bộ thành công');
    } else {
      Alert.alert('Lỗi', result.error || 'Không thể đồng bộ');
    }
  };

  const handleLogout = async () => {
    await authService.logout();
    onLogout();
  };

  const renderTaskItem = ({ item }: { item: WorkTask }) => (
    <TaskCard task={item} onPress={handleTaskPress} />
  );

  const renderPendingLog = ({ item }: { item: QueuedLog }) => (
    <View style={styles.pendingLogItem}>
      <Text style={styles.pendingLogText}>
        Việc #{item.taskId} - {item.syncStatus === 'uploading' ? 'Đang gửi...' : item.syncStatus === 'failed' ? `Lỗi (${item.retryCount} lần)` : 'Chờ gửi'}
      </Text>
      {item.errorMessage && (
        <Text style={styles.errorText}>{item.errorMessage}</Text>
      )}
      {item.syncStatus === 'failed' && item.retryCount < 3 && (
        <Button title="Thử lại" variant="secondary" onPress={() => handleRetryLog(item)} />
      )}
    </View>
  );

  if (isLoading) {
    return (
      <View style={styles.centered}>
        <Text style={styles.loadingText}>Đang tải...</Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Việc hôm nay</Text>
        <TouchableOpacity onPress={handleLogout}>
          <Text style={styles.logoutButton}>Đăng xuất</Text>
        </TouchableOpacity>
      </View>

      {pendingLogs.length > 0 && (
        <View style={styles.pendingSection}>
          <Text style={styles.sectionTitle}>Chưa đồng bộ ({pendingLogs.length})</Text>
          <FlatList
            data={pendingLogs}
            renderItem={renderPendingLog}
            keyExtractor={item => item.localId}
            horizontal
            showsHorizontalScrollIndicator={false}
          />
        </View>
      )}

      <FlatList
        data={tasks}
        renderItem={renderTaskItem}
        keyExtractor={item => String(item.id)}
        contentContainerStyle={styles.list}
        refreshControl={
          <RefreshControl refreshing={isRefreshing} onRefresh={handleRefresh} />
        }
        ListEmptyComponent={
          <View style={styles.emptyContainer}>
            <Text style={styles.emptyText}>Không có việc hôm nay</Text>
          </View>
        }
      />

      <Modal visible={showLogModal} animationType="slide" presentationStyle="pageSheet">
        <ScrollView style={styles.modalContainer}>
          <View style={styles.modalHeader}>
            <Text style={styles.modalTitle}>{selectedTask ? taskTitle(selectedTask) : ''}</Text>
            <TouchableOpacity onPress={() => setShowLogModal(false)}>
              <Text style={styles.closeButton}>Đóng</Text>
            </TouchableOpacity>
          </View>

          <View style={styles.modalContent}>
            {selectedTask?.metadata?.requires_photo && (
              <View style={styles.photoHint}>
                <Text style={styles.photoHintText}>Nhiệm vụ yêu cầu chụp ảnh thực tế</Text>
              </View>
            )}

            <TextInput
              style={styles.notesInput}
              placeholder="Ghi chú thực tế (tùy chọn)"
              value={notes}
              onChangeText={setNotes}
              multiline
              numberOfLines={4}
              textAlignVertical="top"
            />

            <View style={styles.photoSection}>
              <Text style={styles.photoSectionTitle}>Ảnh đính kèm</Text>
              <View style={styles.photoRow}>
                {photoUris.map((uri, index) => (
                  <View key={uri} style={styles.photoThumb}>
                    <Image source={{ uri }} style={styles.photoImage} />
                    <TouchableOpacity
                      style={styles.removePhotoButton}
                      onPress={() => handleRemovePhoto(index)}
                    >
                      <Text style={styles.removePhotoText}>✕</Text>
                    </TouchableOpacity>
                  </View>
                ))}
                <TouchableOpacity style={styles.addPhotoButton} onPress={handlePickPhoto}>
                  <Text style={styles.addPhotoText}>+ Chụp ảnh</Text>
                </TouchableOpacity>
              </View>
            </View>

            <View style={styles.actionButtons}>
              {selectedTask?.status === 'planned' && (
                <Button title="Nhận việc" onPress={() => selectedTask && handleAcceptTask(selectedTask)} />
              )}
              {selectedTask?.status === 'assigned' && (
                <Button title="Bắt đầu làm" onPress={() => selectedTask && handleStartTask(selectedTask)} />
              )}
              <Button
                title={isSubmitting ? 'Đang gửi...' : 'Gửi nhật ký'}
                onPress={handleSubmitLog}
                disabled={isSubmitting}
              />
            </View>
          </View>
        </ScrollView>
      </Modal>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f3f4f6',
  },
  centered: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingText: {
    fontSize: 16,
    color: '#6b7280',
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 12,
    backgroundColor: '#16a34a',
  },
  headerTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: '#ffffff',
  },
  logoutButton: {
    fontSize: 14,
    color: '#ffffff',
  },
  pendingSection: {
    backgroundColor: '#fef3c7',
    padding: 12,
  },
  sectionTitle: {
    fontSize: 14,
    fontWeight: '600',
    color: '#92400e',
    marginBottom: 8,
  },
  pendingLogItem: {
    backgroundColor: '#ffffff',
    borderRadius: 8,
    padding: 12,
    marginRight: 8,
    minWidth: 150,
  },
  pendingLogText: {
    fontSize: 13,
    color: '#374151',
    marginBottom: 4,
  },
  errorText: {
    fontSize: 12,
    color: '#dc2626',
    marginBottom: 4,
  },
  list: {
    padding: 16,
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingTop: 60,
  },
  emptyText: {
    fontSize: 16,
    color: '#6b7280',
  },
  modalContainer: {
    flex: 1,
    backgroundColor: '#f3f4f6',
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 16,
    borderBottomWidth: 1,
    borderBottomColor: '#e5e7eb',
    backgroundColor: '#ffffff',
  },
  modalTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: '#1f2937',
    flex: 1,
  },
  closeButton: {
    fontSize: 14,
    color: '#6b7280',
  },
  modalContent: {
    padding: 16,
  },
  photoHint: {
    backgroundColor: '#fef3c7',
    borderRadius: 8,
    padding: 12,
    marginBottom: 16,
  },
  photoHintText: {
    color: '#92400e',
    fontSize: 14,
  },
  notesInput: {
    backgroundColor: '#ffffff',
    borderRadius: 8,
    padding: 12,
    fontSize: 15,
    minHeight: 100,
    textAlignVertical: 'top',
    borderWidth: 1,
    borderColor: '#d1d5db',
    marginBottom: 16,
  },
  photoSection: {
    marginBottom: 16,
  },
  photoSectionTitle: {
    fontSize: 14,
    fontWeight: '600',
    color: '#374151',
    marginBottom: 8,
  },
  photoRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
  },
  photoThumb: {
    width: 80,
    height: 80,
    marginRight: 8,
    marginBottom: 8,
    borderRadius: 8,
    overflow: 'hidden',
  },
  photoImage: {
    width: '100%',
    height: '100%',
  },
  removePhotoButton: {
    position: 'absolute',
    top: 2,
    right: 2,
    backgroundColor: 'rgba(0,0,0,0.6)',
    borderRadius: 10,
    width: 20,
    height: 20,
    justifyContent: 'center',
    alignItems: 'center',
  },
  removePhotoText: {
    color: '#ffffff',
    fontSize: 12,
  },
  addPhotoButton: {
    width: 80,
    height: 80,
    borderRadius: 8,
    borderWidth: 2,
    borderColor: '#d1d5db',
    borderStyle: 'dashed',
    justifyContent: 'center',
    alignItems: 'center',
  },
  addPhotoText: {
    fontSize: 11,
    color: '#6b7280',
    textAlign: 'center',
  },
  actionButtons: {
    gap: 12,
  },
});
