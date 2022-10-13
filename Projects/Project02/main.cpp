#include <iostream>
#include <fstream>
#include <vector>
#include <string>
#include <sstream>
#include<cmath>
#include <iomanip>
using namespace std;
struct Sigma {
	vector<int>bin;
	bool exist = 0;
};

void read(vector<vector<int>>& bf, vector<vector<int>>& dc, int inputn, int outputn, int bfn, vector<char> in, vector<char> out, string pla_file, string dot_file);
void sigma(vector<vector<int>>& bf, vector<vector<int>>& dc, string dot_file);
void simple(vector<Sigma> sigma, vector<vector<int>> dc, string dot_file);
void sort(vector<Sigma>& sigma);
bool check(vector<int> a, vector<int> b);
void combine(vector<int>& a, vector<int> b);
void petrick(vector<Sigma> original_sigma, vector<Sigma> sigma, string dot_file);
bool sop_check(vector<int> sop, int n);
bool same(vector<vector<int>>temp, vector<int>b);
void output(vector<Sigma> essentail, vector<Sigma> sigma, vector<vector<int >> sop, string dot_file);
int main(int argc, char* argv[]) {
	int inputn = 0, outputn = 0, bfn = 0;
	vector<char> in, out;
	if (argc < 3) {
		return 0;
	}
	string pla_file = argv[1];
	string dot_file = argv[2];
	vector<vector<int>> bf;//bool_function
	vector<vector<int>> dc;//don't care
	read(bf, dc, inputn, outputn, bfn, in, out, pla_file, dot_file);
	sigma(bf, dc, dot_file);
}

void read(vector<vector<int>>& bf, vector<vector<int>>& dc, int inputn, int outputn, int bfn, vector<char> in, vector<char> out, string pla_file, string dot_file) {
	//開檔
	fstream ifs(pla_file, std::ios::in);
	fstream ofs(dot_file, std::ios::out);
	if (!ifs.is_open()) {
		cout << "Failed to open file.\n";
	}
	else {
		string mode;
		while (ifs >> mode) {
			if (mode == ".e") { break; }//.e ==end跳出
			//input n
			else if (mode == ".i") {
				ifs >> inputn;
			}
			//output n
			else if (mode == ".o") {
				ifs >> outputn;
			}
			//intput name
			else if (mode == ".ilb") {
				char temp;
				for (int i = 0; i < inputn; ++i) {
					ifs >> temp;
					in.push_back(temp);
				}
			}
			//output name
			else if (mode == ".ob") {
				char temp;
				for (int i = 0; i < outputn; ++i) {
					ifs >> temp;
					out.push_back(temp);
				}
			}
			//bool_function
			else if (mode == ".p") {
				ifs >> bfn;
				int n = bfn;
				while (n--) {
					char temp;
					vector<int> simpled;
					for (int i = 0; i < inputn; ++i) {
						ifs >> temp;
						if (temp != '-') { simpled.push_back((temp - '0')); }//turn to int
						else { simpled.push_back(2); }//隨意項就放2
					}
					ifs >> temp;//sigma or don't care
					if (temp == '1') { bf.push_back(simpled); }
					else if (temp == '-') { dc.push_back(simpled); bf.push_back(simpled); }
				}
			}
		}
	}
	//輸出
	ofs << ".i " << inputn << "\n";
	ofs << ".o " << outputn << "\n";
	ofs << ".ilb";
	for (int i = 0; i < in.size(); ++i) {
		ofs << " " << in[i];
	}ofs << "\n";
	ofs << ".ob " << out[0] << "\n";
	ofs.close();
	ifs.close();
}

void simple(vector<Sigma> sigma, vector<vector<int>> dc, string dot_file) {
	bool have_simple = 1;
	vector<Sigma> original_sigma = sigma;
	while (have_simple) {
		int n = sigma.size();
		vector<Sigma> simpled;
		have_simple = 0;
		for (int i = 0; i < n; ++i) {
			for (int j = i + 1; j < n; ++j) {
				if (check(sigma[i].bin, sigma[j].bin)) {
					//複製一個A 化檢後放入下次要化簡的是式子
					have_simple = 1;//有執行化簡
					sigma[i].exist = 1; sigma[j].exist = 1;//被使用過的
					Sigma temp;
					temp.bin = sigma[i].bin;
					combine(temp.bin, sigma[j].bin);
					simpled.push_back(temp);
				}
			}
		}
		//放入未使用的項
		for (int i = 0; i < n; ++i) {
			if (sigma[i].exist == 0) simpled.push_back(sigma[i]);
		}
		sort(sigma);
		sigma = simpled;
		//test
		/*for (int i = 0; i < simpled.size(); ++i) {
			for (int j = simpled[i].bin.size()-1; j >=0 ; --j) {
				cout << simpled[i].bin[j];
			}
			cout << " " << simpled[i].exist << "\n";
		}
		cout<<"\n";*/
	}
	//如果don;t care未被削減就刪除
	for (int i = 0; i < dc.size(); ++i) {
		for (int j = 0; j < sigma.size(); ++j) {
			for (int k = 0; k < sigma[j].bin.size(); ++k) {
				if (k == (sigma[j].bin.size() - 1) && dc[i][k] == sigma[j].bin[k]) {
					sigma.erase(sigma.begin() + j);
				}
				if (dc[i][k] != sigma[j].bin[k]) { break; }
			}
		}
	}
	for (int i = 0; i < dc.size(); ++i) {
		for (int j = 0; j < original_sigma.size(); ++j) {
			for (int k = 0; k < original_sigma[j].bin.size(); ++k) {
				if (k == (original_sigma[j].bin.size() - 1) && dc[i][k] == original_sigma[j].bin[k]) {
					original_sigma.erase(original_sigma.begin() + j);
					break;
				}
				else if (dc[i][k] != original_sigma[j].bin[k]) { break; }
			}
		}
	}

	petrick(original_sigma, sigma, dot_file);
}
//a & b whether can be simpled or not
bool check(vector<int> a, vector<int> b) {
	bool diff = 0;//different times
	int n = a.size();
	for (int i = 0; i < n; ++i) {
		if (a[i] != b[i] && diff == 1) { return 0; }
		else if (a[i] != b[i]) { diff = 1; }
	}
	return 1;
}
//將不同的地方改成don't care
void combine(vector<int>& a, vector<int> b) {
	int n = a.size();
	for (int i = 0; i < n; ++i) {
		if (a[i] != b[i]) {
			a[i] = 2;
		}
	}
}

void output(vector<Sigma> essentail, vector<Sigma> sigma, vector<vector<int >> sop, string dot_file) {
	fstream ofs;
	ofs.open(dot_file, ios::out | ios::app);
	ofs << ".p " << (essentail.size() + sop[0].size()) << "\n";
	for (int i = 0; i < essentail.size(); ++i) {
		for (int j = essentail[i].bin.size() - 1; j >= 0; --j) {
			if (essentail[i].bin[j] == 2) ofs << "-";
			else { ofs << essentail[i].bin[j]; }
		}
		ofs << " 1\n";
	}
	for (int i = 0; i < sop[0].size(); ++i) {
		for (int j = sigma[0].bin.size() - 1; j >= 0; --j) {
			if (sigma[sop[0][i]].bin[j] == 2) { ofs << "-"; }
			else { ofs << sigma[sop[0][i]].bin[j]; }
		}
		ofs << " 1\n";
	}
	ofs << ".e\n";

}

//此sop式是否有此元素
bool sop_check(vector<int> sop, int n) {

	for (int i = 0; i < sop.size(); ++i) {
		if (sop[i] == n)return 0;
	}
	return 1;
}
//b是否出現在temp
bool same(vector<vector<int>>temp, vector<int>b) {
	int ex = 0;
	if (temp.size() == 0) { return 0; }
	for (int i = 0; i < temp.size(); ++i) {
		ex = 0;
		for (int j = 0; j < b.size(); ++j) {
			for (int k = 0; k < temp[i].size(); ++k) {
				if (b[j] == temp[i][k]) { ex += 1; }
			}
		}
		if (ex == temp[i].size()) { return 1; }
	}
	return 0;
}
void petrick(vector<Sigma> original_sigma, vector<Sigma> sigma, string dot_file) {
	int n = original_sigma.size();
	vector<vector<int >> sat;//每項PI滿足哪幾項原始sigma
	vector<int>push(n, 0);
	vector<Sigma> essential_PI;
	// find essential PI
	for (int i = 0; i < n; ++i) {
		original_sigma[i].exist = 0;
	}
	for (int i = 0; i < sigma.size(); ++i) {
		sat.push_back(push);
	}
	for (int i = 0; i < sigma.size(); ++i) {
		for (int j = 0; j < n; ++j) {
			for (int k = 0; k < sigma[i].bin.size(); ++k) {
				if (sigma[i].bin[k] == 2) {
					if (k == sigma[i].bin.size() - 1) { sat[i][j] = 1; }
					continue;
				}
				else if (sigma[i].bin[k] != original_sigma[j].bin[k]) { break; }
				else if (k == sigma[i].bin.size() - 1) sat[i][j] = 1;
			}
		}
	}
	for (int i = 0; i < n; ++i) {
		int times = 0;//被滿足幾次
		Sigma temp;
		int PI_n;
		for (int j = 0; j < sigma.size(); ++j) {
			if (sat[j][i] == 1) {
				times += 1;
				PI_n = j;
			}
		}
		if (times == 1) {
			temp.bin = sigma[PI_n].bin;
			sigma[PI_n].exist = 1;
			essential_PI.push_back(temp);
			for (int k = 0; k < n; ++k) {
				if (sat[PI_n][k] == 1) {
					original_sigma[k].exist = 1;
				}
			}
		}
	}

	//find Extraction of a minimum cover
	vector<vector<int >> pos;
	for (int i = 0; i < n; ++i) {
		vector<int>temp;
		for (int j = 0; j < sigma.size(); ++j) {
			if (original_sigma[i].exist == 1) { continue; }//已經被必要項圈到 就不用
			else if (sat[j][i] == 1) { temp.push_back(j); }
		}
		if (original_sigma[i].exist == 0) { pos.push_back(temp); }
	}

	//每列相乘 每行相加 sop
	vector<vector<int >> sop;
	for (int i = 0; i < pos.size(); ++i) {
		vector<vector<int >> temp1;
		for (int j = 0; j < pos[i].size(); ++j) {
			vector<int>temp2;
			for (int k = 0; k < sop.size(); ++k) {
				//如果沒有此元素 複製原本的並加上
				temp2 = sop[k];

				if (sop_check(sop[k], pos[i][j])) {
					temp2.push_back(pos[i][j]);
				}
				temp1.push_back(temp2);
			}
			if (sop.size() == 0) {
				for (int h = 0; h < pos[i].size(); ++h) {
					vector<int>temp3;
					temp3.push_back(pos[i][h]);
					temp1.push_back(temp3);
				}
				break;
			}
		}
		sop = temp1;
	}

	//找出最小項
	int min = 9999;
	for (int i = 0; i < sop.size(); ++i) {
		if (sop[i].size() < min) { min = sop[i].size(); }
	}
	vector<vector<int>> temp1;
	for (int i = 0; i < sop.size(); ++i) {
		if (sop[i].size() == min) {
			temp1.push_back(sop[i]);
		}
	}
	sop = temp1;

	//刪除相同項
	temp1.clear();
	for (int i = 0; i < sop.size(); ++i) {
		if (!same(temp1, sop[i])) {

			temp1.push_back(sop[i]);
		}
	}
	sop = temp1;

	output(essential_PI, sigma, sop, dot_file);
	//test
	/*cout << "sigma \n";
	for (int i = 0; i < sigma.size(); ++i) {
		for (int j = sigma[i].bin.size() - 1; j >= 0; --j) {
			cout << sigma[i].bin[j];
		}
		cout << " " << sigma[i].exist << "\n";
	}
	cout << "\n";

	cout << "original_sigma \n";
	for (int i = 0; i < original_sigma.size(); ++i) {
		for (int j = original_sigma[i].bin.size() - 1; j >= 0; --j) {
			cout << original_sigma[i].bin[j];
		}
		cout << " " << original_sigma[i].exist << "\n";
	}
	cout << "\n";

	cout << "sat  \n";
	for (int i = 0; i < sigma.size(); ++i) {
		for (int j = 0; j < n; ++j) {
			cout << sat[i][j] << " ";
		}
		cout << "\n";
	}

	cout << "essential PI \n";
	for (int i = 0; i < essential_PI.size(); ++i) {
		for (int j = 0; j < essential_PI[0].bin.size(); ++j) {
			cout << essential_PI[i].bin[j] << " ";
		}
		cout << "\n";
	}*/

	//   cout << "pos\n";
	   //for (int i = 0; i < pos.size(); ++i) {
	   //	for (int j = 0; j < pos[i].size(); ++j) {
	   //		cout << pos[i][j] << " ";
	   //	}
	   //	cout << "\n";
	   //}


	   //cout << "sop\n";
	   //for (int i = 0; i < sop.size(); ++i) {
	   //	for (int j = 0; j < sop[i].size(); ++j) {
	   //		cout << sop[i][j] << " ";
	   //	}
	   //	cout << "\n";
	   //}
}

void sigma(vector<vector<int>>& bf, vector<vector<int>>& dc, string dot_file) {
	vector<vector<int>> binary;
	vector<int> a;
	vector<Sigma> sigma;
	int n = bf[0].size();
	int times = 1;
	//create binary
	for (int i = 0; i < n; ++i) {
		a.push_back(0);
	}
	while (times != (pow(2, n) + 1)) {
		binary.push_back(a);
		for (int i = 0; i < n; ++i) {
			if (times % (int)pow(2, i) == 0) {
				if (a[i] == 0) a[i] = 1;
				else a[i] = 0;
			}
		}
		times++;
	}

	//find sigma
	//將binary比對bf 成立就是sigma
	//帶入二進位數字 求各個結果
	for (int i = 0; i < pow(2, n); ++i) {
		Sigma temp;
		for (int k = 0; k < bf.size(); ++k) {
			for (int j = 0; j < n; ++j) {
				//don't care skip
				temp.bin = binary[i];
				if (bf[k][j] == 2) {
					if (j == n - 1) {
						//temp.bin = binary[i];
						temp.exist = true;
					}
					continue;
				}
				else if (binary[i][n - j - 1] != bf[k][j]) { //n-j-1因為產出之binary左右相反
					break;
				}
				else if (j == n - 1) {
					//temp.bin = binary[i]; 
					temp.exist = true;
				}
			}
		}
		if (temp.exist == 1) {
			sigma.push_back(temp);
		}
	}
	//test
	//for (int i = 0; i < sigma.size(); ++i) {
	//	for (int j = 0; j < sigma[i].bin.size(); ++j) {
	//		cout << sigma[i].bin[j];
	//	}
	//	cout << " " << sigma[i].exist << "\n";
	//}
	//cout << "\n\n";
	sort(sigma);
	//test
	//for (int i = 0; i < sigma.size(); ++i) {
	//	for (int j = sigma[i].bin.size()-1; j >=0; --j) {
	//		cout << sigma[i].bin[j];
	//	}
	//	cout << " " << sigma[i].exist << "\n";
	//}
	//cout << "\n\n";
	simple(sigma, dc, dot_file);
}
void sort(vector<Sigma>& sigma) {
	vector<Sigma> alt;
	int n = sigma[0].bin.size();
	for (int k = 0; k < n + 1; k++) {
		for (int i = 0; i < sigma.size(); ++i) {
			int one_times = 0;
			for (int j = 0; j < n; ++j) {
				if (sigma[i].bin[j] == 1) one_times += 1;
			}
			if (one_times == k) alt.push_back(sigma[i]);
		}
	}
	sigma = alt;
}