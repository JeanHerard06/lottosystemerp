import 'package:flutter/material.dart';
import '../api_client.dart';

class FichesScreen extends StatefulWidget {
  const FichesScreen({super.key});
  @override
  State<FichesScreen> createState() => _FichesScreenState();
}

class _FichesScreenState extends State<FichesScreen> {
  List items = [];
  Future<void> load() async {
    final res = await ApiClient().get('fiches/list.php');
    setState(() => items = res['data'] ?? []);
  }
  @override
  void initState(){ super.initState(); load(); }
  @override
  Widget build(BuildContext context){
    return Scaffold(appBar: AppBar(title: const Text('Mes fiches')), body: ListView.builder(itemCount: items.length, itemBuilder: (_, i){ final f=items[i]; return ListTile(title: Text(f['fiche_code']), subtitle: Text('${f['status']} • ${f['created_at']}'), trailing: Text('${f['total_amount']}')); }));
  }
}
