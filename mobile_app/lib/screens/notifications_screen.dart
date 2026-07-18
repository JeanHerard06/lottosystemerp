import 'package:flutter/material.dart';
import '../api_client.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  List notifications = [];
  int unreadCount = 0;
  bool loading = false;
  bool unreadOnly = false;

  @override
  void initState() {
    super.initState();
    load();
  }

  Future<void> load() async {
    setState(() => loading = true);
    try {
      final res = await ApiClient().get('notifications/index.php${unreadOnly ? '?unread=1' : ''}');
      final data = res['data'] as Map<String, dynamic>? ?? {};
      if (!mounted) return;
      setState(() {
        notifications = data['notifications'] as List? ?? [];
        unreadCount = int.tryParse('${data['unread_count'] ?? 0}') ?? 0;
      });
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }

  Future<void> markRead(Object? id) async {
    try {
      await ApiClient().postJson('notifications/mark_read.php', {'id': id});
      await load();
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    }
  }

  Future<void> markAllRead() async {
    try {
      await ApiClient().postJson('notifications/mark_all_read.php', {});
      await load();
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Toutes les notifications sont lues.')));
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    }
  }

  Future<void> deleteNotification(Object? id) async {
    try {
      await ApiClient().postJson('notifications/delete.php', {'id': id});
      await load();
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    }
  }

  IconData iconFor(String type) {
    switch (type) {
      case 'success':
        return Icons.check_circle_outline;
      case 'warning':
        return Icons.warning_amber_rounded;
      case 'danger':
        return Icons.error_outline;
      default:
        return Icons.notifications_none;
    }
  }

  Color colorFor(BuildContext context, String type) {
    switch (type) {
      case 'success':
        return Colors.green;
      case 'warning':
        return Colors.orange;
      case 'danger':
        return Colors.red;
      default:
        return Theme.of(context).colorScheme.primary;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Notifications${unreadCount > 0 ? ' ($unreadCount)' : ''}'),
        actions: [
          IconButton(onPressed: load, icon: const Icon(Icons.refresh)),
          IconButton(onPressed: unreadCount > 0 ? markAllRead : null, icon: const Icon(Icons.done_all)),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: load,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            if (loading) const LinearProgressIndicator(),
            Row(children: [
              Expanded(
                child: SegmentedButton<bool>(
                  segments: const [
                    ButtonSegment(value: false, label: Text('Toutes')),
                    ButtonSegment(value: true, label: Text('Non lues')),
                  ],
                  selected: {unreadOnly},
                  onSelectionChanged: (s) {
                    setState(() => unreadOnly = s.first);
                    load();
                  },
                ),
              ),
            ]),
            const SizedBox(height: 12),
            if (notifications.isEmpty)
              const Card(
                child: ListTile(
                  leading: Icon(Icons.notifications_off_outlined),
                  title: Text('Aucune notification'),
                  subtitle: Text('Les alertes de lottery, cash, claims et commissions apparaîtront ici.'),
                ),
              )
            else
              for (final n in notifications)
                Card(
                  color: n['read_at'] == null ? Theme.of(context).colorScheme.primaryContainer.withOpacity(.25) : null,
                  child: Dismissible(
                    key: ValueKey('notification_${n['id']}'),
                    direction: DismissDirection.endToStart,
                    background: Container(
                      alignment: Alignment.centerRight,
                      padding: const EdgeInsets.only(right: 20),
                      color: Colors.red,
                      child: const Icon(Icons.delete, color: Colors.white),
                    ),
                    confirmDismiss: (_) async {
                      await deleteNotification(n['id']);
                      return true;
                    },
                    child: ListTile(
                      leading: Icon(iconFor('${n['type'] ?? 'info'}'), color: colorFor(context, '${n['type'] ?? 'info'}')),
                      title: Text('${n['title'] ?? ''}', style: TextStyle(fontWeight: n['read_at'] == null ? FontWeight.bold : FontWeight.normal)),
                      subtitle: Text('${n['message'] ?? ''}\n${n['created_at'] ?? ''}'),
                      isThreeLine: true,
                      trailing: n['read_at'] == null ? IconButton(onPressed: () => markRead(n['id']), icon: const Icon(Icons.mark_email_read_outlined)) : null,
                      onTap: n['read_at'] == null ? () => markRead(n['id']) : null,
                    ),
                  ),
                ),
          ],
        ),
      ),
    );
  }
}
