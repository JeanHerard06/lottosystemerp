import 'package:flutter/material.dart';
import '../api_client.dart';

class TiragesScreen extends StatefulWidget {
  const TiragesScreen({super.key});
  @override
  State<TiragesScreen> createState() => _TiragesScreenState();
}

class _TiragesScreenState extends State<TiragesScreen> {
  List items = [];
  Future<void> load() async {
    final res = await ApiClient().get('tirages_list.php');
    setState(() => items = res['data'] ?? []);
  }
  @override void initState(){ super.initState(); load(); }
  @override Widget build(BuildContext context){
    return Scaffold(
      appBar: AppBar(title: const Text('Tirages')),
      body: RefreshIndicator(
        onRefresh: load,
        child: ListView.builder(
          itemCount: items.length,
          itemBuilder: (_, i){
            final t=items[i];
            return Card(child: ListTile(
              leading: const Icon(Icons.casino),
              title: Text('${t['draw_name']}'),
              subtitle: Text('${t['draw_date']}'),
              trailing: Column(mainAxisAlignment: MainAxisAlignment.center, children: [Text('${t['first_number'] ?? '-'}', style: const TextStyle(fontWeight: FontWeight.bold)), Text('${t['second_number'] ?? ''} ${t['third_number'] ?? ''}')]),
            ));
          },
        ),
      ),
    );
  }
}
